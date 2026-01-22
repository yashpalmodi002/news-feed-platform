<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    private string $apiKey;
    private string $baseUrl = 'https://api.openai.com/v1';

    public function __construct()
    {
        $this->apiKey = config('services.openai.key');
    }

    public function generateSummary(string $title, string $content): string
    {
        try {
            $prompt = "Summarize the following news article in 2-3 concise sentences:\n\n";
            $prompt .= "Title: {$title}\n\n";
            $prompt .= "Content: " . substr($content, 0, 1000) . "\n\n";
            $prompt .= "Summary:";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($this->baseUrl . '/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => 150,
                'temperature' => 0.7,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return trim($data['choices'][0]['message']['content'] ?? '');
            } else {
                Log::error('OpenAI API error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return '';
            }
        } catch (\Exception $e) {
            Log::error('OpenAI exception', ['message' => $e->getMessage()]);
            return '';
        }
    }
}