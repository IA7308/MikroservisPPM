<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DocGarudaAuthorService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.garuda.url');
    }

    public function getGaruda($params = [])
    {
        try {
            $response = Http::timeout(3)
                ->get("{$this->baseUrl}/api/doc-garuda-author", $params);
                
            return $response->json();
        } catch (\Exception $e) {
            Log::error("DocGarudaAuthor service error: " . $e->getMessage());
                return ['error' => "DocGarudaAuthor service unavailable {$this->baseUrl}"];
        }
    }

    public function getGarudaAC($params = [])
    {
        try {
            $response = Http::timeout(3)
                ->get("{$this->baseUrl}/api/doc-garuda-author/AC", $params);
                
            return $response->json();
        } catch (\Exception $e) {
            Log::error("DocGarudaAuthor service error: " . $e->getMessage());
                return ['error' => "DocGarudaAuthor service unavailable {$this->baseUrl}"];
        }
    }

    public function getgarudaById($id)
    {
        try {
            $response = Http::timeout(3)
                ->get("{$this->baseUrl}/api/doc-garuda-author/{$id}");
                
            return $response->json();
        } catch (\Exception $e) {
            Log::error("DocGarudaAuthor service error: " . $e->getMessage());
            return ['error' => 'DocGarudaAuthor service unavailable'];
        }
    }

    public function getgarudaByAuthorId($authorid)
    {
        try {
            $response = Http::timeout(3)
                ->get("{$this->baseUrl}/api/doc-garuda-author/{$authorid}");
                
            return $response->json();
        } catch (\Exception $e) {
            Log::error("DocGarudaAuthor service error: " . $e->getMessage());
            return ['error' => 'DocGarudaAuthor service unavailable'];
        }
    }

    public function creategarudaDocService(Request $request)
    {
        try {
            $response = Http::timeout(5)
                ->post("{$this->baseUrl}/api/doc-garuda-author", $request->all());
                
            return $response->json();
        } catch (\Exception $e) {
            Log::error("DocGarudaAuthor service error: " . $e->getMessage());
            return ['error' => 'DocGarudaAuthor service unavailable'];
        }            
    }

    public function updategarudaDocService(Request $request, $id)
    {
        try {
            $response = Http::timeout(5)
                ->put("{$this->baseUrl}/api/doc-garuda-author/{$id}", $request->all());
                
            return $response->json();
        } catch (\Exception $e) {
            Log::error("DocGarudaAuthor service error: " . $e->getMessage());
            return ['error' => 'DocGarudaAuthor service unavailable'];
        }            
    }

    public function deletegarudaDocService($id)
    {
        try {
            $response = Http::timeout(5)
                ->delete("{$this->baseUrl}/api/doc-garuda-author/{$id}");
                
            return $response->json();
        } catch (\Exception $e) {
            Log::error("DocGarudaAuthor service error: " . $e->getMessage());
            return ['error' => 'DocGarudaAuthor service unavailable'];
        }            
    }
}