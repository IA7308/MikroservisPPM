<?php

namespace App\Services;

use App\Models\DocBookAuthor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class DocBookAuthorService
{
    public static function getAllBookDocService()
    {
        try {
            $BookDoc = DocBookAuthor::all();

            return response()->json([
                'message' => 'BookDoc retrieved successfully',
                'status' => 'true',
                'data' => $BookDoc,
            ]);
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'message' => 'Failed to retrieve BookDoc',
                    'status' => 'false',
                    'error' => $th->getMessage(),
                ],
                200,
            );
        }
    }

    public static function getPaginateACBook(Request $request){
        try {
            $keyword = $request->query('keyword');
            $year = $request->query('year');
            $category = $request->query('category');
            $items = $request->query('items', 10);

            $cacheKey = 'doc_book_authors:' . md5(json_encode([
                'keyword' => $keyword,
                'year' => $year,
                'category' => $category,
                'items' => $items,
                'page' => $request->query('page', 1)
            ]));

            $docBookAuthor = Cache::remember($cacheKey, 300, function () use ($keyword, $year, $category, $items) {
                $query = DocBookAuthor::query();
                
                // Filter berdasarkan keyword
                if ($keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('title', 'like', "%$keyword%")
                        ->orWhere('authors', 'like', "%$keyword%");
                    });
                }
                
                // Filter berdasarkan tahun
                if ($year) {
                    $query->where('year', $year);
                }
                
                // Filter berdasarkan kategori
                if ($category) {
                    $query->where('category', 'like', "%$category%");
                }
                
                $results = $query->paginate($items);

                $profileIds = $results->pluck('profile_id')->unique()->filter()->toArray();
    
                if (!empty($profileIds)) {
                    try {
                        // Fetch profile data from ProfileService
                        $profileResponse = Http::get(env('PROFILESERVICE_URL') . '/api/profiles', [
                            'ids' => $profileIds,
                            'fields' => 'id,nidn,fullname,gelar_depan,gelar_belakang,country,image,programs_id'
                        ]);
                        
                        $profiles = $profileResponse->successful() ? $profileResponse->json() : [];
                        
                        // Fetch program data from ProfileProgramService
                        $programIds = collect($profiles)->pluck('programs_id')->unique()->filter()->toArray();
                        
                        if (!empty($programIds)) {
                            $programResponse = Http::get(env('PROFILEPROGRAMSERVICE_URL') . '/api/programs', [
                                'ids' => $programIds,
                                'fields' => 'code_pddikti,faculty_id,name_id,name_en'
                            ]);
                            
                            $programs = $programResponse->successful() ? $programResponse->json() : [];
                            
                            // Map programs to their IDs for easy access
                            $programsMap = collect($programs)->keyBy('id');
                        }
                        
                        // Map profiles to their IDs and attach programs
                        $profilesMap = collect($profiles)->map(function ($profile) use ($programsMap) {
                            if (isset($profile['programs_id']) && isset($programsMap[$profile['programs_id']])) {
                                $profile['program'] = $programsMap[$profile['programs_id']];
                            }
                            return $profile;
                        })->keyBy('id');
                        
                        // Attach profiles to the results
                        $results->getCollection()->transform(function ($item) use ($profilesMap) {
                            if (isset($item->profile_id) && isset($profilesMap[$item->profile_id])) {
                                $item->profileAuthor = $profilesMap[$item->profile_id];
                            }
                            return $item;
                        });
                    } catch (\Exception $e) {
                        // Log error if needed
                        \Log::error('Failed to fetch profile data: ' . $e->getMessage());
                    }
                }
                
                return $results;
            });
            return response()->json([
                'message' => 'Doc Book Author retrieved successfully',
                'status' => true,
                'data' => $docBookAuthor,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to retrieve Doc Book Author',
                'status' => false,
                'error' => $th->getMessage(),
            ]);
        }
    }    
    public static function getPaginateBookDocService(Request $request)
    {
        try {
            $keyword = $request->query('keyword');
            $year = $request->query('year');
            $category = $request->query('category');
            $items = $request->query('items', 10);

            $docBookAuthor = DocBookAuthor::query()
                ->when($keyword, function ($query) use ($keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('title', 'like', "%$keyword%")
                            ->orWhere('authors', 'like', "%$keyword%");
                    });
                })
                ->when($year, function ($query, $year) {
                    return $query->where('year', $year);
                })
                ->when($category, function ($query, $category) {
                    return $query->where('category', 'like', "%$category%");
                })
                ->paginate($items);

            $authorIds = $docBookAuthor->pluck('author_id')->unique()->toArray();

            $profileAuthors = [];
            if (!empty($authorIds)) {
                $profileResponse = Http::get(env('PROFILEAUTHOR_SERVICE_URL').'/api/profile-author', [
                    'ids' => implode(',', $authorIds)
                ]);
                
                if ($profileResponse->successful()) {
                    $profileAuthors = collect($profileResponse->json()['data'] ?? [])
                        ->keyBy('id');
                }
            }

            $programIds = collect($profileAuthors)->pluck('programs_id')->unique()->filter()->toArray();

            $programs = [];
            if (!empty($programIds)) {
                $programResponse = Http::get(env('PROFILEAUTHOR_SERVICE_URL').'/api/profile-program', [
                    'ids' => implode(',', $programIds)
                ]);
                
                if ($programResponse->successful()) {
                    $programs = collect($programResponse->json()['data'] ?? [])
                        ->keyBy('code_pddikti');
                }
            }

            $transformedData = $docBookAuthor->getCollection()->map(function ($item) use ($profileAuthors, $programs) {
                $authorData = $profileAuthors[$item->author_id] ?? null;
                $programData = $authorData ? ($programs[$authorData['programs_id']] ?? null) : null;
                
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'authors' => $item->authors,
                    'year' => $item->year,
                    'category' => $item->category,
                    'profile_author' => $authorData ? [
                        'nidn' => $authorData['nidn'],
                        'fullname' => $authorData['fullname'],
                        'gelar_depan' => $authorData['gelar_depan'],
                        'gelar_belakang' => $authorData['gelar_belakang'],
                        'country' => $authorData['country'],
                        'image' => $authorData['image'],
                    ] : null,
                    'program' => $programData ? [
                        'code_pddikti' => $programData['code_pddikti'],
                        'name_id' => $programData['name_id'],
                        'name_en' => $programData['name_en'],
                    ] : null
                ];
            });

            // Set collection yang sudah ditransformasi
            $docBookAuthor->setCollection($transformedData);

            return response()->json([
                'message' => 'Doc Book Author retrieved successfully',
                'status' => true,
                'data' => $docBookAuthor,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to retrieve Doc Book Author',
                'status' => false,
                'error' => $th->getMessage(),
            ]);
        }
    }


    public static function getDocBookAuthorById($id)
    {
        try {
            $docBookAuthor = DocBookAuthor::findOrFail($id);

            return response()->json([
                'message' => 'DocBookAuthor retrieved successfully',
                'status' => 'true',
                'data' => $docBookAuthor,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to retrieve BookDoc',
                'status' => 'false',
                'error' => $th->getMessage(),
            ]);
        }
    }

    public static function getDocBookAuthorByAuthorId($authorId)
    {
        try {
            $docBookAuthor = DocBookAuthor::where('author_id', $authorId)->get();

            if ($docBookAuthor->isEmpty()) {
                return response()->json([
                    'message' => 'No BookDocs found for the provided author ID.',
                    'status' => 'false',
                    'data' => [],
                ]);
            }

            return response()->json([
                'message' => 'DocBookAuthor retrieved successfully',
                'status' => 'true',
                'data' => $docBookAuthor,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to retrieve BookDoc',
                'status' => 'false',
                'error' => $th->getMessage(),
            ]);
        }
    }

    public static function createBookDocServiceAC(Request $request)
    {
        try {
            $validated = $request->validate([
                'author_id' => 'required|int',
                'category' => 'required|string',
                'isbn' => 'required|int',
                'title' => 'required|string',
                'authors' => 'required|string',
                'place' => 'required|string',
                'publisher' => 'required|string',
                'year' => 'required|int',
            ]);
             // Ambil data buku yang sudah ada di cache (jika ada)
            $cachedBooks = Cache::get('batch_books', []);

            // Tambahkan data baru ke cache
            $cachedBooks[] = $validated;

            // Simpan kembali ke cache
            Cache::put('batch_books', $cachedBooks, 60); // Simpan selama 60 menit

            // Jika jumlah data di cache mencapai batas tertentu (misalnya, 10), lakukan bulk insert
            if (count($cachedBooks) >= 10) {
                // Bulk insert ke database
                DocBookAuthor::insert($cachedBooks);

                // Hapus cache setelah data disimpan ke database
                Cache::forget('batch_books');
            }

            return response()->json([
                'message' => 'BookDoc created successfully',
                'status' => 'true',
                'data' => $validated,
            ]);
        } catch (ValidationException $e) {
            return response()->json(
                [
                    'message' => 'Validation Error',
                    'status' => 'false',
                    'error' => $e->errors(),
                ],
                200,
            );
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'message' => 'Failed to create BookDoc',
                    'status' => 'false',
                    'error' => $th->getMessage(),
                ],
                200,
            );
        }
    }
    public static function createBookDocService(Request $request)
    {
        try {
            $validated = $request->validate([
                'author_id' => 'required|int',
                'category' => 'required|string',
                'isbn' => 'required|int',
                'title' => 'required|string',
                'authors' => 'required|string',
                'place' => 'required|string',
                'publisher' => 'required|string',
                'year' => 'required|int',
            ]);
            $validate = DocBookAuthor::create($validated);

            return response()->json([
                'message' => 'BookDoc created successfully',
                'status' => 'true',
                'data' => [
                    'author_id' => $validate->author_id,
                    'category' => $validate->category,
                    'isbn' => $validate->isbn,
                    'title' => $validate->title,
                    'authors' => $validate->authors,
                    'place' => $validate->place,
                    'publisher' => $validate->publisher,
                    'year' => $validate->year,
                    'updated_at' => $validate->updated_at,
                    'created_at' => $validate->created_at,
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json(
                [
                    'message' => 'Validation Error',
                    'status' => 'false',
                    'error' => $e->errors(),
                ],
                200,
            );
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'message' => 'Failed to create BookDoc',
                    'status' => 'false',
                    'error' => $th->getMessage(),
                ],
                200,
            );
        }
    }

    public static function updateBookDocServiceAC(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'author_id' => 'required|int',
                'category' => 'required|string',
                'isbn' => 'required|int',
                'title' => 'required|string',
                'authors' => 'required|string',
                'place' => 'required|string',
                'publisher' => 'required|string',
                'year' => 'required|int',
            ]);

             // Mengambil data dengan caching
            $bookDoc = Cache::remember('book_doc_'.$id, now()->addHours(1), function() use ($id) {
                return DocBookAuthor::findOrFail($id);
            });

            // Update data dan sekaligus update cache
            $bookDoc->update($validated);
            Cache::put('book_doc_'.$id, $bookDoc, 300);
            Cache::forget('book_doc_'.$id);

            return response()->json(
                [
                    'message' => 'BookDoc updated successfully',
                    'status' => 'true',
                    'data' => $bookDoc,
                ],
                200,
            );
        } catch (ValidationException $e) {
            return response()->json(
                [
                    'message' => 'Validation Error',
                    'status' => 'false',
                    'error' => $e->errors(),
                ],
                200,
            );
        } catch (\Throwable $e) {
            return response()->json(
                [
                    'message' => 'Failed to update BookDoc',
                    'status' => 'false',
                    'error' => $e->getMessage(),
                ],
                500,
            );
        }
    }
    public static function updateBookDocService(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'author_id' => 'required|int',
                'category' => 'required|string',
                'isbn' => 'required|int',
                'title' => 'required|string',
                'authors' => 'required|string',
                'place' => 'required|string',
                'publisher' => 'required|string',
                'year' => 'required|int',
            ]);
            $bookDoc = DocBookAuthor::findOrFail($id);
            $bookDoc->update($validated);

            return response()->json(
                [
                    'message' => 'BookDoc updated successfully',
                    'status' => 'true',
                    'data' => $bookDoc,
                ],
                200,
            );
        } catch (ValidationException $e) {
            return response()->json(
                [
                    'message' => 'Validation Error',
                    'status' => 'false',
                    'error' => $e->errors(),
                ],
                200,
            );
        } catch (\Throwable $e) {
            return response()->json(
                [
                    'message' => 'Failed to update BookDoc',
                    'status' => 'false',
                    'error' => $e->getMessage(),
                ],
                500,
            );
        }
    }
    public static function deleteBookDocServiceAC(string $id)
    {
        try {
            $bookDoc = DocBookAuthor::findOrFail($id);
            $bookDoc->delete();
            Cache::forget('book_doc_' . $id);

            return response()->json(
                [
                    'status' => true,
                    'message' => 'BookDoc deleted successfully',
                ],
                200,
            );
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'message' => 'Failed to delete BookDoc',
                    'status' => 'false',
                    'error' => $th->getMessage(),
                ],
                500,
            );
        }
    }
    public static function deleteBookDocService(string $id)
    {
        try {
            $bookDoc = DocBookAuthor::findOrFail($id);
            $bookDoc->delete();

            return response()->json(
                [
                    'status' => true,
                    'message' => 'BookDoc deleted successfully',
                ],
                200,
            );
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'message' => 'Failed to delete BookDoc',
                    'status' => 'false',
                    'error' => $th->getMessage(),
                ],
                500,
            );
        }
    }
}
