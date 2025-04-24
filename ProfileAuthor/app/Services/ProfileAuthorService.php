<?php

namespace App\Services;


use Illuminate\Http\Request;
use App\Models\ProfileAuthor;
use Illuminate\Support\Facades\Validator;
use Storage;

class ProfileAuthorService
{
    
    public static function getProfileAuthor()
    {
        try {
            $data = ProfileAuthor::with(['affiliation', 'program', 'scopusAuthors', 'wosAuthors', 'garudaAuthors', 'googleAuthors', 'docResearchAuthors'])->get();
            return response()->json([
                'status' => true,
                'message' => 'Successfully found Daftar Author',
                'data' => $data
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'failed to get Daftar Author',
                'error' => $th->getMessage()
            ], 500);
        }

    }

    public static function getPaginatedProfileAuthor(Request $request)
    {
        try {
            $keyword = $request->query('keyword');
            $faculty = $request->query('faculty');
            $perPage = $request->query('per_page', 10);

            $author = ProfileAuthor::with(['affiliation', 'program', 'scopusAuthors', 'wosAuthors', 'garudaAuthors', 'googleAuthors'])
                ->when($faculty, function ($query) use ($faculty) {
                    $query->whereHas('program', function ($q) use ($faculty) {
                        $q->where('faculty_id', $faculty);
                    });
                })
                ->when($keyword, function ($query) use ($keyword) {
                    $query->where('fullname', 'like', '%' . $keyword . '%');
                })
                ->paginate($perPage);
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Profile Author retrieved successfully',
                    'data' => $author,
                ],
                200,
            );
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Error',
                    'data' => $th->getMessage()
                ],
                500
            );
        }
    }

    public static function getProfileAuthorById($id)
    {
        //
        try {
            $article = ProfileAuthor::with([
                'affiliation',
                'program',
                'scopusAuthors',
                'wosAuthors',
                'garudaAuthors',
                'googleAuthors',
            ])->findOrFail($id);

            $article["contributions"] = [
                "total_publication" =>
                    ($article->scopusAuthors['total_document'] ?? 0) +
                    ($article->wosAuthors['total_document'] ?? 0) +
                    ($article->garudaAuthors['total_document'] ?? 0) +
                    ($article->googleAuthors['total_document'] ?? 0),
                "total_research" => $article->docResearchAuthors->count(),
                "total_communityservice" => $article->communityServices->count(),
                "total_iprs" => $article->iprs->count(),
                "total_book" => $article->books->count(),
                "total_product" => $article->docResearchAuthors->sum(function ($docAuthor) {
                    return $docAuthor->products->count();
                }),
                "total_publication" =>
                    $article->docScopusAuthors->count() +
                    $article->docWosAuthors->count() +
                    $article->docGarudaAuthors->count() +
                    $article->docGoogleAuthors->count()
            ];

            $article->makeHidden([
                'docResearchAuthors',
                'communityServices',
                'iprs',
                'books',
                'docScopusAuthors',
                'docWosAuthors',
                'docGarudaAuthors',
                'docGoogleAuthors'
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Successfully found Daftar Author with id : ' . $id,
                'data' => $article
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'failed to get Daftar Author  with id : ' . $id,
                'error' => $th->getMessage()
            ], 500);
        }

    }

    public function createProfileAuthor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'programs_id' => 'required|integer|exists:profile_programs,id',
            'affiliation_id' => 'required|integer|exists:affiliation,id',
            'nidn' => 'required|string|max:255',
            'fullname' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'academic_grade_raw' => 'required|string|max:255',
            'academic_grade' => 'required|string|max:255',
            'gelar_depan' => 'nullable|string|max:255',
            'gelar_belakang' => 'nullable|string|max:255',
            'last_education' => 'required|string|max:255',
            'sinta_score_v2_overall' => 'required|numeric',
            'sinta_score_v2_3year' => 'required|numeric',
            'sinta_score_v3_overall' => 'required|numeric',
            'sinta_score_v3_3year' => 'required|numeric',
            'affiliation_score_v3_overall' => 'required|numeric',
            'affiliation_score_v3_3year' => 'required|numeric',
            'image' => 'nullable|image',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        try {

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('images', 'public');
            }


            $author = ProfileAuthor::create($data);

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Author created successfully',
                    'data' => $author,
                ],
                201,
            );
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Error creating author',
                    'data' => $th->getMessage()
                ],
                500
            );
        }
    }

    public function updateAuthor(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'programs_id' => 'required|integer|exists:profile_programs,id',
            'affiliation_id' => 'required|integer|exists:affiliation,id',
            'nidn' => 'required|string|max:255',
            'fullname' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'academic_grade_raw' => 'required|string|max:255',
            'academic_grade' => 'required|string|max:255',
            'gelar_depan' => 'nullable|string|max:255',
            'gelar_belakang' => 'nullable|string|max:255',
            'last_education' => 'required|string|max:255',
            'sinta_score_v2_overall' => 'required|numeric',
            'sinta_score_v2_3year' => 'required|numeric',
            'sinta_score_v3_overall' => 'required|numeric',
            'sinta_score_v3_3year' => 'required|numeric',
            'affiliation_score_v3_overall' => 'required|numeric',
            'affiliation_score_v3_3year' => 'required|numeric',
            'image' => 'nullable|image',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        try {
            $author = ProfileAuthor::findOrFail($id);

            if ($request->hasFile('image')) {
                if ($author->image && Storage::disk('public')->exists($author->image)) {
                    Storage::disk('public')->delete($author->image);
                }
                $data['image'] = $request->file('image')->store('images', 'public');
            }

            $author->update($data);

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Author updated successfully',
                    'data' => $author,
                ],
                200,
            );
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Error updating author',
                    'data' => $th->getMessage()
                ],
                500
            );
        }
    }

    public function deleteAuthor($id)
    {
        try {
            $author = ProfileAuthor::findOrFail($id);

            if ($author->image) {
                Storage::disk('public')->delete($author->image);
            }

            $author->delete();
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Author deleted successfully',
                    'data' => $author,
                ],
                200,
            );
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Error deleting author',
                    'data' => $th->getMessage()
                ],
                500
            );
        }
    }

}
