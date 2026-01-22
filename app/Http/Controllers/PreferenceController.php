<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\UserPreference;
use Illuminate\Http\Request;

class PreferenceController extends Controller
{
    public function index()
    {
        $categories = Category::where('is_active', true)->get();
        
        // FIXED LINE - specify which table's id column
        $userPreferences = auth()->user()->preferences()->pluck('category_id')->toArray();

        return view('preferences.index', compact('categories', 'userPreferences'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'categories' => 'required|array|min:1',
            'categories.*' => 'exists:categories,id',
        ]);

        $user = auth()->user();

        // Delete existing preferences
        UserPreference::where('user_id', $user->id)->delete();

        // Create new preferences
        foreach ($request->categories as $categoryId) {
            UserPreference::create([
                'user_id' => $user->id,
                'category_id' => $categoryId,
            ]);
        }

        return redirect()->route('feed.index')
            ->with('success', 'Your preferences have been updated!');
    }
}