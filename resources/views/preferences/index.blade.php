@extends('layouts.app')

@section('title', 'Preferences')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-lg shadow-sm p-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Your Interests</h1>
        <p class="text-gray-600 mb-8">Select the topics you want to follow</p>

        <form method="POST" action="{{ route('preferences.update') }}">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
                @foreach($categories as $category)
                    <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all
                        {{ in_array($category->id, $userPreferences) ? 'border-blue-600 bg-blue-50' : 'border-gray-300 hover:border-gray-400' }}">
                        <input type="checkbox" 
                               name="categories[]" 
                               value="{{ $category->id }}"
                               {{ in_array($category->id, $userPreferences) ? 'checked' : '' }}
                               class="sr-only peer"
                               onchange="this.parentElement.classList.toggle('border-blue-600'); this.parentElement.classList.toggle('bg-blue-50');">
                        <div class="flex items-center">
                            <span class="text-3xl mr-3">{{ $category->icon }}</span>
                            <div>
                                <div class="font-semibold text-gray-900">{{ $category->name }}</div>
                                <div class="text-sm text-gray-500">{{ $category->description }}</div>
                            </div>
                        </div>
                        <div class="absolute top-2 right-2 hidden peer-checked:block">
                            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </label>
                @endforeach
            </div>

            @error('categories')
                <p class="text-red-600 text-sm mb-4">{{ $message }}</p>
            @enderror

            <div class="flex justify-end space-x-4">
                <a href="{{ route('feed.index') }}" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Save Preferences
                </button>
            </div>
        </form>
    </div>
</div>
@endsection