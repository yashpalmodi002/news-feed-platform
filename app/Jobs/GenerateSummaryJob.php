<?php

namespace App\Jobs;

use App\Models\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateSummaryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;

    private Article $article;

    public function __construct(Article $article)
    {
        $this->article = $article;
    }

    public function handle(): void
    {
        // Determine which AI service to use
        $useMock = config('services.use_mock_services', true);
        $aiService = $useMock 
            ? app(\App\Services\MockAIService::class)
            : app(\App\Services\OpenAIService::class);

        try {
            $content = $this->article->content ?? $this->article->description;
            
            if (empty($content)) {
                Log::warning('No content available for article', ['id' => $this->article->id]);
                $this->article->update([
                    'summary' => $this->article->description ?? 'No summary available.',
                    'status' => 'processed',
                    'processed_at' => now(),
                ]);
                return;
            }

            $summary = $aiService->generateSummary(
                $this->article->title,
                $content
            );

            if (empty($summary)) {
                // Fallback to description if AI fails
                $summary = $this->article->description ?? 'Summary generation failed.';
                $status = 'partial';
            } else {
                $status = 'processed';
            }

            $this->article->update([
                'summary' => $summary,
                'status' => $status,
                'processed_at' => now(),
            ]);

            Log::info('Summary generated for article', ['id' => $this->article->id]);
        } catch (\Exception $e) {
            Log::error('Error generating summary', [
                'article_id' => $this->article->id,
                'error' => $e->getMessage()
            ]);

            $this->article->update([
                'status' => 'failed',
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }
}