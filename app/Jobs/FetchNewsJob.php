<?php

namespace App\Jobs;

use App\Models\Article;
use App\Models\Category;
use App\Models\Source;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FetchNewsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $newsService;
    private int $limit;

    public function __construct(int $limit = 50)
    {
        $this->limit = $limit;
    }

    public function handle(): void
    {
        // Determine which news service to use
        $useMock = config('services.use_mock_services', true);
        $this->newsService = $useMock 
            ? app(\App\Services\MockNewsService::class)
            : app(\App\Services\NewsAPIService::class);

        // Get active categories
        $categories = Category::active()->get();

        if ($categories->isEmpty()) {
            Log::warning('No active categories found for news fetching');
            return;
        }

        // Fetch news
        $result = $this->newsService->fetchNews($categories->toArray(), $this->limit);

        if ($result['status'] !== 'ok') {
            Log::error('Failed to fetch news', ['result' => $result]);
            return;
        }

        // Process and store articles
        $articlesStored = 0;
        $articlesSkipped = 0;

        foreach ($result['articles'] as $articleData) {
            try {
                // Check if article already exists
                if (Article::where('url', $articleData['url'])->exists()) {
                    $articlesSkipped++;
                    continue;
                }

                // Find or create source
                $source = Source::firstOrCreate(
                    ['name' => $articleData['source']['name']],
                    ['is_active' => true]
                );

                // Determine category (simple logic - can be improved)
                $category = $this->determineCategory($articleData['title'] . ' ' . $articleData['description']);

                // Create article
                $article = Article::create([
                    'category_id' => $category->id,
                    'source_id' => $source->id,
                    'title' => $articleData['title'],
                    'description' => $articleData['description'] ?? '',
                    'content' => $articleData['content'] ?? '',
                    'url' => $articleData['url'],
                    'image_url' => $articleData['urlToImage'] ?? null,
                    'author' => $articleData['author'] ?? 'Unknown',
                    'published_at' => Carbon::parse($articleData['publishedAt']),
                    'status' => 'pending',
                ]);

                // Dispatch job to generate summary
                GenerateSummaryJob::dispatch($article);

                $articlesStored++;
            } catch (\Exception $e) {
                Log::error('Error storing article', [
                    'url' => $articleData['url'] ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('News fetch completed', [
            'stored' => $articlesStored,
            'skipped' => $articlesSkipped,
        ]);
    }

    private function determineCategory(string $text): Category
    {
        $text = strtolower($text);
        $keywords = [
            'technology' => ['tech', 'software', 'ai', 'computer', 'digital', 'app', 'startup'],
            'business' => ['business', 'economy', 'finance', 'market', 'stock', 'investment'],
            'sports' => ['sport', 'game', 'player', 'team', 'championship', 'athlete'],
            'health' => ['health', 'medical', 'doctor', 'disease', 'treatment', 'fitness'],
            'science' => ['science', 'research', 'study', 'scientist', 'discovery'],
            'entertainment' => ['movie', 'film', 'music', 'celebrity', 'entertainment', 'actor'],
        ];

        foreach ($keywords as $categorySlug => $terms) {
            foreach ($terms as $term) {
                if (str_contains($text, $term)) {
                    $category = Category::where('slug', $categorySlug)->first();
                    if ($category) {
                        return $category;
                    }
                }
            }
        }

        // Default to technology if no match
        return Category::where('slug', 'technology')->first() 
            ?? Category::first();
    }
}