@extends('layouts.app')

@section('title', 'My Feed')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Your Personalized Feed</h1>
        <p class="mt-2 text-sm text-gray-600">Articles based on your interests</p>
    </div>

    <!-- Category Filter -->
    <div class="mb-6 flex space-x-2 overflow-x-auto pb-2">
        <a href="{{ route('feed.index') }}" class="px-4 py-2 bg-blue-600 text-white rounded-full text-sm font-medium whitespace-nowrap">
            All
        </a>
        @foreach($categories as $category)
            <a href="{{ route('feed.category', $category) }}" class="px-4 py-2 bg-white text-gray-700 hover:bg-gray-100 rounded-full text-sm font-medium whitespace-nowrap border border-gray-300">
                {{ $category->icon }} {{ $category->name }}
            </a>
        @endforeach
    </div>

    <!-- Articles Grid -->
    <div class="space-y-6">
        @forelse($articles as $article)
            <article class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden">
                <div class="md:flex">
                    @if($article->image_url)
                        <div class="md:flex-shrink-0">
                            <img class="h-48 w-full md:w-48 object-cover" src="{{ $article->image_url }}" alt="{{ $article->title }}">
                        </div>
                    @endif
                    <div class="p-6 flex-1">
                        <div class="flex items-center text-sm text-gray-500 mb-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $article->category->icon }} {{ $article->category->name }}
                            </span>
                            <span class="mx-2">•</span>
                            <span>{{ $article->source?->name }}</span>
                            <span class="mx-2">•</span>
                            <span>{{ $article->published_at->diffForHumans() }}</span>
                        </div>
                        
                        <h2 class="text-xl font-semibold text-gray-900 mb-2">
                            <a href="{{ route('articles.show', $article) }}" class="hover:text-blue-600">
                                {{ $article->title }}
                            </a>
                        </h2>
                        
                        @if($article->summary)
                            <p class="text-gray-600 mb-4">
                                <span class="font-medium text-sm text-blue-600">AI Summary:</span>
                                {{ Str::limit($article->summary, 200) }}
                            </p>
                        @endif
                        
                        <div class="flex items-center space-x-4">
                            <button onclick="toggleSave({{ $article->id }})" 
                                    id="save-btn-{{ $article->id }}"
                                    class="text-sm text-gray-500 hover:text-gray-700 flex items-center">
                                <span class="mr-1">💾</span> Save
                            </button>
                            <a href="{{ route('articles.show', $article) }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                                <span class="mr-1">👁️</span> Read More
                            </a>
                            <a href="{{ $article->url }}" target="_blank" class="text-sm text-gray-500 hover:text-gray-700 flex items-center">
                                <span class="mr-1">🔗</span> Source
                            </a>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                <div class="text-6xl mb-4">📰</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No articles found</h3>
                <p class="text-gray-600 mb-4">Try adjusting your preferences or check back later for new content.</p>
                <a href="{{ route('preferences.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Update Preferences
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $articles->links() }}
    </div>
</div>

@push('scripts')
<script>
function toggleSave(articleId) {
    fetch(`/articles/${articleId}/save`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        const btn = document.getElementById(`save-btn-${articleId}`);
        if (data.saved) {
            btn.innerHTML = '<span class="mr-1">✅</span> Saved';
        } else {
            btn.innerHTML = '<span class="mr-1">💾</span> Save';
        }
    });
}
</script>
@endpush
@endsection