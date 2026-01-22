@extends('layouts.app')

@section('title', $category->name . ' - News Feed')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">{{ $category->icon }} {{ $category->name }}</h1>
        <p class="mt-2 text-sm text-gray-600">{{ $category->description }}</p>
    </div>

    <!-- Same content as feed.index -->
    <div class="space-y-6">
        @forelse($articles as $article)
            <article class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ $article->title }}</h2>
                <p class="text-gray-600">{{ $article->summary }}</p>
            </article>
        @empty
            <p class="text-center text-gray-600">No articles found in this category.</p>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $articles->links() }}
    </div>
</div>
@endsection