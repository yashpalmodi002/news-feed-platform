<?php

namespace App\Services;

use App\Models\Article;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FeedService
{
    public function getPersonalizedFeed(User $user, int $perPage = 20): LengthAwarePaginator
    {
        // Get user's preferred category IDs
        $categoryIds = $user->preferredCategories()->pluck('categories.id');
        
        // Get article IDs user has already read
        $readArticleIds = $user->readingHistory()->pluck('article_id');
        
        // Query for personalized feed
        return Article::query()
            ->with(['category', 'source'])
            ->whereIn('category_id', $categoryIds)
            ->whereNotIn('id', $readArticleIds)
            ->where('status', 'processed')
            ->orderBy('published_at', 'desc')
            ->paginate($perPage);
    }

    public function getArticlesByCategory(int $categoryId, int $perPage = 20): LengthAwarePaginator
    {
        return Article::query()
            ->with(['category', 'source'])
            ->where('category_id', $categoryId)
            ->where('status', 'processed')
            ->orderBy('published_at', 'desc')
            ->paginate($perPage);
    }

    public function getTrendingArticles(int $limit = 10): \Illuminate\Database\Eloquent\Collection|array
    {
        return Article::query()
            ->with(['category', 'source'])
            ->withCount('readingHistory')
            ->where('status', 'processed')
            ->where('published_at', '>=', now()->subDays(3))
            ->orderBy('reading_history_count', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getSavedArticles(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return Article::query()
            ->with(['category', 'source'])
            ->whereHas('savedByUsers', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orderBy('published_at', 'desc')
            ->paginate($perPage);
    }
}