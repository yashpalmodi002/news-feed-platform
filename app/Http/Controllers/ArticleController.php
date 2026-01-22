<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ReadingHistory;
use App\Models\SavedArticle;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function show(Article $article)
    {
        $article->load(['category', 'source']);
        
        $isRead = auth()->user()->hasReadArticle($article->id);
        $isSaved = auth()->user()->hasSavedArticle($article->id);

        // Get related articles from same category
        $relatedArticles = Article::where('category_id', $article->category_id)
            ->where('id', '!=', $article->id)
            ->where('status', 'processed')
            ->orderBy('published_at', 'desc')
            ->limit(5)
            ->get();

        return view('articles.show', compact('article', 'isRead', 'isSaved', 'relatedArticles'));
    }

    public function markAsRead(Request $request, Article $article)
    {
        $user = auth()->user();

        ReadingHistory::updateOrCreate(
            [
                'user_id' => $user->id,
                'article_id' => $article->id,
            ],
            [
                'read_at' => now(),
                'time_spent' => $request->input('time_spent', 0),
            ]
        );

        return response()->json(['success' => true]);
    }

    public function toggleSave(Request $request, Article $article)
    {
        $user = auth()->user();

        $saved = SavedArticle::where('user_id', $user->id)
            ->where('article_id', $article->id)
            ->first();

        if ($saved) {
            $saved->delete();
            $isSaved = false;
        } else {
            SavedArticle::create([
                'user_id' => $user->id,
                'article_id' => $article->id,
            ]);
            $isSaved = true;
        }

        return response()->json(['saved' => $isSaved]);
    }
}