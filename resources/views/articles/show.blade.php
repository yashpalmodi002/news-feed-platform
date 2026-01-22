@extends('layouts.app')

@section('title', $article->title)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="{{ route('feed.index') }}" class="text-blue-600 hover:text-blue-800 flex items-center">
            ← Back to Feed
        </a>
    </div>

    <article class="bg-white rounded-lg shadow-sm overflow-hidden">
        @if($article->image_url)
            <img src="{{ $article->image_url }}" alt="{{ $article->title }}" class="w-full h-96 object-cover">
        @endif

        <div class="p-8">
            <div class="flex items-center text-sm text-gray-500 mb-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ $article->category->icon }} {{ $article->category->name }}
                </span>
                <span class="mx-2">•</span>
                <span>{{ $article->source?->name }}</span>
                <span class="mx-2">•</span>
                <span>{{ $article->published_at->format('F j, Y') }}</span>
            </div>

            <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $article->title }}</h1>

            @if($article->author)
                <p class="text-gray-600 mb-6">By {{ $article->author }}</p>
            @endif

            <div class="flex items-center space-x-4 mb-8 pb-6 border-b">
                <button onclick="toggleSave({{ $article->id }})" 
                        id="save-btn"
                        class="px-4 py-2 {{ $isSaved ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }} rounded-md hover:bg-gray-200 flex items-center">
                    <span class="mr-2">{{ $isSaved ? '✅' : '💾' }}</span>
                    {{ $isSaved ? 'Saved' : 'Save Article' }}
                </button>
                <a href="{{ $article->url }}" target="_blank" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 flex items-center">
                    <span class="mr-2">🔗</span> Read Full Article
                </a>
            </div>

            @if($article->summary)
                <div class="bg-blue-50 border-l-4 border-blue-600 p-6 mb-8">
                    <h3 class="text-lg font-semibold text-blue-900 mb-2">AI-Generated Summary</h3>
                    <p class="text-gray-700">{{ $article->summary }}</p>
                </div>
            @endif

            @if($article->content)
                <div class="prose max-w-none text-gray-700 mb-8">
                    {{ $article->content }}
                </div>
            @elseif($article->description)
                <div class="prose max-w-none text-gray-700 mb-8">
                    {{ $article->description }}
                </div>
            @endif

            <div class="bg-gray-50 p-6 rounded-lg">
                <p class="text-sm text-gray-600">
                    This is a summary. For the complete article, visit the 
                    <a href="{{ $article->url }}" target="_blank" class="text-blue-600 hover:underline">original source</a>.
                </p>
            </div>
        </div>
    </article>

    @if($relatedArticles->count() > 0)
        <div class="mt-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Related Articles</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($relatedArticles as $related)
                    <a href="{{ route('articles.show', $related) }}" class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow p-4">
                        <h3 class="font-semibold text-gray-900 mb-2">{{ $related->title }}</h3>
                        <p class="text-sm text-gray-600">{{ Str::limit($related->summary, 100) }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
// Track reading time
let startTime = Date.now();

window.addEventListener('beforeunload', function() {
    const timeSpent = Math.floor((Date.now() - startTime) / 1000);
    
    if (timeSpent > 5) { // Only track if user spent more than 5 seconds
        fetch('{{ route("articles.read", $article) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ time_spent: timeSpent })
        });
    }
});

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
        const btn = document.getElementById('save-btn');
        if (data.saved) {
            btn.innerHTML = '<span class="mr-2">✅</span> Saved';
            btn.className = 'px-4 py-2 bg-green-100 text-green-800 rounded-md hover:bg-green-200 flex items-center';
        } else {
            btn.innerHTML = '<span class="mr-2">💾</span> Save Article';
            btn.className = 'px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 flex items-center';
        }
    });
}
</script>
@endpush
@endsection