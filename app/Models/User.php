<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relationships
    public function preferences()
    {
        return $this->hasMany(\App\Models\UserPreference::class);
    }

    public function readingHistory()
    {
        return $this->hasMany(\App\Models\ReadingHistory::class);
    }

    public function savedArticles()
    {
        return $this->hasMany(\App\Models\SavedArticle::class);
    }

    public function preferredCategories()
    {
        return $this->belongsToMany(
            \App\Models\Category::class, 
            'user_preferences',
            'user_id',
            'category_id'
        );
    }

    // Helper methods
    public function hasReadArticle($articleId)
    {
        return $this->readingHistory()->where('article_id', $articleId)->exists();
    }

    public function hasSavedArticle($articleId)
    {
        return $this->savedArticles()->where('article_id', $articleId)->exists();
    }
}