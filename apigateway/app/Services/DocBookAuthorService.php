<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DocBookAuthorService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.book.url');
    }

    public function getBook($params = [])
    {
        try {
            $response = Http::timeout(3)
                ->get("{$this->baseUrl}/api/doc-book-author", $params);
                
            return $response->json();
        } catch (\Exception $e) {
            Log::error("DocBookAuthor service error: " . $e->getMessage());
                return ['error' => "DocBookAuthor service unavailable {$this->baseUrl}"];
        }
    }

    public function getBookAC($params = [])
    {
        try {
            $response = Http::timeout(3)
                ->get("{$this->baseUrl}/api/doc-book-author/AC", $params);
                
            return $response->json();
        } catch (\Exception $e) {
            Log::error("DocBookAuthor service error: " . $e->getMessage());
                return ['error' => "DocBookAuthor service unavailable {$this->baseUrl}"];
        }
    }

    public function getBookById($id)
    {
        try {
            $response = Http::timeout(3)
                ->get("{$this->baseUrl}/api/doc-book-author/{$id}");
                
            return $response->json();
        } catch (\Exception $e) {
            Log::error("DocBookAuthor service error: " . $e->getMessage());
            return ['error' => 'DocBookAuthor service unavailable'];
        }
    }

    public function getBookByAuthorId($authorid)
    {
        try {
            $response = Http::timeout(3)
                ->get("{$this->baseUrl}/api/doc-book-author/{$authorid}");
                
            return $response->json();
        } catch (\Exception $e) {
            Log::error("DocBookAuthor service error: " . $e->getMessage());
            return ['error' => 'DocBookAuthor service unavailable'];
        }
    }

    public function createBookDocService(Request $request)
    {
        try {
            $response = Http::timeout(5)
                ->post("{$this->baseUrl}/api/doc-book-author", $request->all());
                
            return $response->json();
        } catch (\Exception $e) {
            Log::error("DocBookAuthor service error: " . $e->getMessage());
            return ['error' => 'DocBookAuthor service unavailable'];
        }            
    }
}