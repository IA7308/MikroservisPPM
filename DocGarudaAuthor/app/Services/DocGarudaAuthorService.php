<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\ProfileAuthor;
use App\Models\DocGarudaAuthor;
use App\Models\LogSyncSinta;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class DocGarudaAuthorService
{
    public static function getAuthorGarudaDoc()
    {
        try {
            $article = DocGarudaAuthor::all();
            return response()->json([
                'status' => true,
                'message' => 'Successfully found Daftar Author GarudaDoc',
                'data' => $article
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'failed to get Daftar Author GarudaDoc',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public static function getPaginatedGarudaDoc(Request $request)
    {
        try {
            $author = $request->query('author');
            $keyword = $request->query('keyword');
            $year = $request->query('year');
            $cited = $request->query('cited');
            $items = $request->query('items', 10);

            $docGarudaAuthor = DocGarudaAuthor::query()
            ->when($keyword, function ($query, $keyword) {
                return $query->where('title', 'like', "%$keyword%");
            })
            ->when($author, function ($query, $author) {
                return $query->where('publisher_name', 'like', "%$author%");
            })
            ->when($year, function ($query, $year) {
                return $query->where('publisher_year', $year);
            })
            ->when($cited, function ($query, $cited) {
                if (preg_match('/([<>])\s*(\d+)/', $cited, $matches)) {
                    $operator = $matches[1];
                    $value = intval($matches[2]);
                    return $query->where('citation', $operator, $value);
                }
            })
            ->paginate($items);

            // Now fetch profile and program data from microservices
            $profileIds = $docGarudaAuthor->getCollection()->pluck('profile_id')->unique()->filter()->toArray();

            if (!empty($profileIds)) {
                try {
                    // Fetch profile data from ProfileService
                    $profileResponse = Http::get(env('PROFILEAUTHOR_SERVICE_URL') . '/api/profile-author', [
                        'ids' => $profileIds,
                        'fields' => 'id,nidn,fullname,gelar_depan,gelar_belakang,country,image,programs_id'
                    ]);
                    
                    $profiles = $profileResponse->successful() ? $profileResponse->json() : [];
                    
                    // Fetch program data from ProfileProgramService if we have profiles with programs_id
                    $programIds = collect($profiles)->pluck('programs_id')->unique()->filter()->toArray();
                    
                    if (!empty($programIds)) {
                        $programResponse = Http::get(env('PROFILEAUTHOR_SERVICE_URL') . '/api/profile-program', [
                            'ids' => $programIds,
                            'fields' => 'code_pddikti,faculty_id,name_id,name_en'
                        ]);
                        
                        $programs = $programResponse->successful() ? $programResponse->json() : [];
                        
                        // Create a map of programs by ID for easy lookup
                        $programsMap = collect($programs)->keyBy('id');
                    }
                    
                    // Create a map of profiles by ID and attach programs if available
                    $profilesMap = collect($profiles)->map(function ($profile) use ($programsMap) {
                        if (isset($profile['programs_id']) && isset($programsMap[$profile['programs_id']])) {
                            $profile['program'] = $programsMap[$profile['programs_id']];
                        }
                        return $profile;
                    })->keyBy('id');
                    
                    // Attach profiles and programs to the main results
                    $docGarudaAuthor->getCollection()->transform(function ($item) use ($profilesMap) {
                        if (isset($item->profile_id) && isset($profilesMap[$item->profile_id])) {
                            $item->profileAuthor = $profilesMap[$item->profile_id];
                        }
                        return $item;
                    });
                    
                } catch (\Exception $e) {
                    // Log error but continue with the main results
                    \Log::error('Failed to fetch profile/program data: ' . $e->getMessage());
                }
            }

            // Handle keyword search for author names if needed (since we can't use orWhereHas anymore)
            if ($keyword) {
                // If we have profiles from the API, filter by name in memory
                if (isset($profilesMap)) {
                    $filtered = $docGarudaAuthor->getCollection()->filter(function ($item) use ($keyword, $profilesMap) {
                        return stripos($item->title, $keyword) !== false || 
                            (isset($item->profile_id) && 
                                isset($profilesMap[$item->profile_id]) && 
                                stripos($profilesMap[$item->profile_id]['fullname'], $keyword) !== false);
                    });
                    
                    // Recreate the paginator with filtered results
                    $docGarudaAuthor = new \Illuminate\Pagination\LengthAwarePaginator(
                        $filtered->values(),
                        $filtered->count(),
                        $items,
                        $request->query('page', 1)
                    );
                }
            }

            return response()->json([
                'message' => 'Doc Garuda Author retrieved successfully',
                'status' => true,
                'data' => $docGarudaAuthor
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to retrieve Doc Garuda Author',
                'status' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public static function getDocGarudaAuthorById($id)
    {
        try {
            $docGarudaAuthor = DocGarudaAuthor::findOrFail($id);

            return response()->json(
                [
                    'message' => 'docGarudaAuthor retrieved successfully',
                    'status' => 'true',
                    'data' => $docGarudaAuthor
                ]
            );
        } catch (\Throwable $e) {
            return response()->json(
                [
                    'message' => 'Failed to retrieve docGarudaAuthor',
                    'status' => 'false',
                    'error' => $e->getMessage(),
                ]
            );
        }
    }

    public static function getdocGarudaAuthorByAuthorId($authorId)
    {
        try {
            $docGarudaAuthor = DocGarudaAuthor::where('author_id', $authorId)->get();

            if ($docGarudaAuthor->isEmpty()) {
                return response()->json([
                    'message' => 'No GarudaDocs found for the provided author ID.',
                    'status' => 'false',
                    'data' => [],
                ]);
            }

            return response()->json(
                [
                    'message' => 'Document retrieved successfully',
                    'status' => 'true',
                    'data' => $docGarudaAuthor
                ]
            );
        } catch (\Throwable $e) {
            return response()->json(
                [
                    'message' => 'Failed to retrieve document',
                    'status' => 'false',
                    'error' => $e->getMessage(),
                ]
            );
        }
    }

    public function createGarudaDoc(Request $request)
    {
        $validatedData = $request->validate([
            'author_id' => 'required|integer',
            'author_order' => 'required|string',
            'accreditation' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'abstract' => 'required|string',
            'publisher_name' => 'required|string|max:255',
            'publish_date' => 'required|date',
            'publish_year' => 'required|integer',
            'doi' => 'nullable|string|max:255',
            'citation' => 'nullable|integer',
            'source' => 'nullable|string|max:255',
            'source_issue' => 'nullable|string|max:255',
            'source_page' => 'nullable|string|max:255',
            'url' => 'required|url|max:255',
        ]);

        try {
            $document = DocGarudaAuthor::create($validatedData);
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Document created successfully',
                    'data' => $document,
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
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Error creating document',
                    'data' => $th->getMessage(),
                ],
                500,
            );
        }
    }

    public function updateGarudaDoc(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'author_id' => 'required|integer',
                'author_order' => 'required|string',
                'accreditation' => 'required|string|max:255',
                'title' => 'required|string|max:255',
                'abstract' => 'required|string',
                'publisher_name' => 'required|string|max:255',
                'publish_date' => 'required|date',
                'publish_year' => 'required|integer',
                'doi' => 'nullable|string|max:255',
                'citation' => 'nullable|integer',
                'source' => 'nullable|string|max:255',
                'source_issue' => 'nullable|string|max:255',
                'source_page' => 'nullable|string|max:255',
                'url' => 'required|url|max:255',
            ]);

            $document = DocGarudaAuthor::findOrFail($id);
            $document->update($validatedData);

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Document updated successfully',
                    'data' => $document->fresh(),
                ],
                200
            );
        } catch (ValidationException $e) {
            return response()->json(
                [
                    'message' => 'Validation Error',
                    'status' => false,
                    'errors' => $e->errors(),
                ],
                422
            );
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Error updating document',
                    'data' => $th->getMessage(),
                ],
                500
            );
        }
    }

    public function deleteGarudaDoc($id)
    {
        try {
            $document = DocGarudaAuthor::findOrFail($id);
            $document->delete();
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Document deleted successfully',
                ],
                200,
            );
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Error deleting document',
                    'data' => $th,
                ],
                500,
            );
        }
    }

    // public static function syncFromSintaDocGarudaAuthor()
    // {
    //     try {
    //         $tokenSinta = LoginSintaService::loginSinta(new Request());
    //         $authors = ProfileAuthor::select('NIDN')->distinct()->get();
    //         $fetchedDocuments = [];

    //         foreach ($authors as $author) {
    //             $totalDocumentsFetched = 0;
    //             $processedTitle = [];

    //             $sintaApi = "http://apisinta.kemdikbud.go.id/v3/dev/271071775/author/garuda/nidn/{$author->NIDN}";

    //             $res = Http::withToken($tokenSinta)->post($sintaApi);

    //             if ($res->failed()) {
    //                 return response()->json(
    //                     [
    //                         'message' => "Failed to Sync Document from SINTA with NIDN : {$author->NIDN}",
    //                         'status' => false,
    //                         'error' => $res->body(),
    //                     ],
    //                     400
    //                 );
    //             }

    //             $data = $res->json();
    //             $docGarudaAuthors = $data['results']['documents'];
    //             $totalDocuments = $data['results']['total'];
    //             $authorId = $data['results']['authors']['author_id'];

    //             foreach ($docGarudaAuthors as $docGarudaAuthor) {
    //                 $title = $docGarudaAuthor['title'];
    //                 if (!in_array($title, $processedTitle)) {
    //                     $processedTitle[] = $title;
    //                     $citation = ($docGarudaAuthor['citation'] === "") ? 0 : (int) $docGarudaAuthor['citation'];


    //                     $fetchedDocuments[] = [
    //                         'author_id' => $authorId,
    //                         'title' => $title,
    //                         'author_order' => $docGarudaAuthor['author_order'] ?? "",
    //                         'accreditation' => $docGarudaAuthor['accreditation'] ?? "",
    //                         'abstract' => $docGarudaAuthor['abstract'] ?? "",
    //                         'publisher_name' => $docGarudaAuthor['publisher_name'] ?? "",
    //                         'publish_date' => $docGarudaAuthor['publish_date'] ?? "",
    //                         'publish_year' => $docGarudaAuthor['publish_year'] ?? "",
    //                         'doi' => $docGarudaAuthor['doi'] ?? "",
    //                         'citation' => $citation,
    //                         'source' => $docGarudaAuthor['source'] ?? "",
    //                         'source_issue' => $docGarudaAuthor['source_issue'] ?? "",
    //                         'source_page' => $docGarudaAuthor['source_page'] ?? "",
    //                         'url' => $docGarudaAuthor['url'] ?? "",
    //                     ];

    //                     DocGarudaAuthor::updateOrCreate(
    //                         [
    //                             'id' => $docGarudaAuthor['id'],
    //                             'author_id' => $authorId,
    //                             'title' => $title
    //                         ],
    //                         [
    //                             'author_order' => $docGarudaAuthor['author_order'] ?? "",
    //                             'accreditation' => $docGarudaAuthor['accreditation'] ?? "",
    //                             'abstract' => $docGarudaAuthor['abstract'] ?? "",
    //                             'publisher_name' => $docGarudaAuthor['publisher_name'] ?? "",
    //                             'publish_date' => $docGarudaAuthor['publish_date'] ?? "",
    //                             'publish_year' => $docGarudaAuthor['publish_year'] ?? "",
    //                             'doi' => $docGarudaAuthor['doi'] ?? "",
    //                             'citation' => $citation,
    //                             'source' => $docGarudaAuthor['source'] ?? "",
    //                             'source_issue' => $docGarudaAuthor['source_issue'] ?? "",
    //                             'source_page' => $docGarudaAuthor['source_page'] ?? "",
    //                             'url' => $docGarudaAuthor['url'] ?? "",
    //                         ]
    //                     );
    //                     $totalDocumentsFetched++;
    //                 }
    //             }

    //             if ($totalDocumentsFetched >= $totalDocuments) {
    //                 break;
    //             }
    //         }


    //         LogSyncSinta::create([
    //             'username' => Auth::user()->username,
    //             'endpoint' => 'Doc Garuda Author',
    //             'status' => 'success',
    //         ]);

    //         return response()->json([
    //             "message" => "Synced doc garuda author data from SINTA successfully",
    //             "status" => true,
    //             "data" => $fetchedDocuments
    //         ], 200);
    //     } catch (\Throwable $e) {
    //         LogSyncSinta::create([
    //             'username' => Auth::user()->username,
    //             'endpoint' => 'Doc Garuda Author',
    //             'status' => 'failed',
    //         ]);
            
    //         return response()->json([
    //             "message" => "Failed to sync doc garuda author data from SINTA",
    //             "status" => "false",
    //             "error" => $e->getMessage(),
    //         ], 500);
    //     }
    // }
}
