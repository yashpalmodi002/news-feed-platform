<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewsAPIService
{
    private string $apiKey;
    private string $baseUrl = 'https://newsapi.org/v2';

    public function __construct()
    {
        $this->apiKey = config('services.newsapi.key');
    }

    public function fetchNews(array $categories, int $limit = 10): array
    {
        $allArticles = [];
        
        foreach ($categories as $category) {
            try {
                $response = Http::get($this->baseUrl . '/top-headlines', [
                    'apiKey' => $this->apiKey,
                    'category' => $category['slug'],
                    'language' => 'en',
                    'pageSize' => ceil($limit / count($categories)),
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $allArticles = array_merge($allArticles, $data['articles'] ?? []);
                } else {
                    Log::error('NewsAPI error for category ' . $category['slug'], [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('NewsAPI exception for category ' . $category['slug'], [
                    'message' => $e->getMessage()
                ]);
            }
        }
        
        return [
            'status' => 'ok',
            'totalResults' => count($allArticles),
            'articles' => array_slice($allArticles, 0, $limit),
        ];
    }
}