<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\PreferenceController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Breeze Dashboard - redirect to feed
Route::get('/dashboard', function () {
    return redirect()->route('feed.index');
})->middleware(['auth', 'verified'])->name('dashboard');

// Breeze Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Authentication Routes (Laravel Breeze provides these)
require __DIR__.'/auth.php';

// Protected Routes
Route::middleware(['auth'])->group(function () {
    
    // Feed Routes
    Route::get('/feed', [FeedController::class, 'index'])->name('feed.index');
    Route::get('/feed/category/{category}', [FeedController::class, 'category'])->name('feed.category');
    Route::get('/feed/saved', [FeedController::class, 'saved'])->name('feed.saved');
    
    // Article Routes
    Route::get('/articles/{article}', [ArticleController::class, 'show'])->name('articles.show');
    Route::post('/articles/{article}/read', [ArticleController::class, 'markAsRead'])->name('articles.read');
    Route::post('/articles/{article}/save', [ArticleController::class, 'toggleSave'])->name('articles.save');
    
    // Preference Routes
    Route::get('/preferences', [PreferenceController::class, 'index'])->name('preferences.index');
    Route::post('/preferences', [PreferenceController::class, 'update'])->name('preferences.update');
});