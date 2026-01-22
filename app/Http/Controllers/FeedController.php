<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Check if user has set preferences
        if ($user->preferences()->count() === 0) {
            return redirect()->route('preferences.index')
                ->with('message', 'Please select your preferred topics first');
        }

        // Get user's preferred category IDs - FIXED LINE
        $categoryIds = $user->preferences()->pluck('category_id')->toArray();
        
        // Get article IDs user has already read
        $readArticleIds = $user->readingHistory()->pluck('article_id')->toArray();
        
        // Query for personalized feed
        $articles = Article::with(['category', 'source'])
            ->whereIn('category_id', $categoryIds)
            ->whereNotIn('id', $readArticleIds)
            ->where('status', 'processed')
            ->orderBy('published_at', 'desc')
            ->paginate(20);

        $categories = Category::where('is_active', true)->get();

        return view('feed.index', compact('articles', 'categories'));
    }

    public function category(Category $category)
    {
        $articles = Article::with(['category', 'source'])
            ->where('category_id', $category->id)
            ->where('status', 'processed')
            ->orderBy('published_at', 'desc')
            ->paginate(20);

        $categories = Category::where('is_active', true)->get();

        return view('feed.category', compact('articles', 'category', 'categories'));
    }

    public function saved()
    {
        $user = auth()->user();
        
        $articles = Article::with(['category', 'source'])
            ->whereHas('savedByUsers', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orderBy('published_at', 'desc')
            ->paginate(20);

        $categories = Category::where('is_active', true)->get();

        return view('feed.saved', compact('articles', 'categories'));
    }
}