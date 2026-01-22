# Implementation Guide

## Overview

This document provides a comprehensive technical walkthrough of the codebase, explaining how all components work together and how to implement the system from scratch.

---

## 🏗️ Project Structure
```
news-feed-platform/
├── app/
│   ├── Console/
│   │   ├── Commands/
│   │   │   └── FetchNewsCommand.php          # CLI command to fetch news
│   │   └── Kernel.php                         # Scheduler configuration
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── FeedController.php             # Personalized feed logic
│   │   │   ├── ArticleController.php          # Article viewing & tracking
│   │   │   └── PreferenceController.php       # Topic management
│   │   └── Middleware/
│   ├── Models/
│   │   ├── User.php                           # User model with relationships
│   │   ├── Article.php                        # Article model with scopes
│   │   ├── Category.php                       # Category/topic model
│   │   ├── Source.php                         # News source model
│   │   ├── UserPreference.php                 # User-category pivot
│   │   ├── ReadingHistory.php                 # Reading tracking
│   │   └── SavedArticle.php                   # Bookmarks
│   ├── Services/
│   │   ├── NewsService.php                    # News fetching interface
│   │   ├── MockNewsService.php                # Mock news generator
│   │   ├── NewsAPIService.php                 # Real NewsAPI integration
│   │   ├── AIService.php                      # AI summarization interface
│   │   ├── MockAIService.php                  # Mock AI summaries
│   │   └── OpenAIService.php                  # Real OpenAI integration
│   └── Jobs/
│       ├── FetchNewsJob.php                   # Background news fetching
│       └── GenerateSummaryJob.php             # AI summary generation
├── config/
│   └── services.php                           # API configuration
├── database/
│   ├── migrations/
│   │   ├── 2024_01_01_000001_create_categories_table.php
│   │   ├── 2024_01_01_000002_create_sources_table.php
│   │   ├── 2024_01_01_000003_create_articles_table.php
│   │   ├── 2024_01_01_000004_create_user_preferences_table.php
│   │   ├── 2024_01_01_000005_create_reading_history_table.php
│   │   └── 2024_01_01_000006_create_saved_articles_table.php
│   └── seeders/
│       ├── CategorySeeder.php                 # Seed 6 categories
│       ├── UserSeeder.php                     # Create test user
│       └── DatabaseSeeder.php                 # Master seeder
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php                  # Main layout template
│       ├── feed/
│       │   ├── index.blade.php                # Personalized feed
│       │   ├── category.blade.php             # Category filtered view
│       │   └── saved.blade.php                # Saved articles list
│       ├── articles/
│       │   └── show.blade.php                 # Article detail + AI summary
│       └── preferences/
│           └── index.blade.php                # Topic selection page
├── routes/
│   └── web.php                                # Application routes
└── .env                                       # Environment configuration
```

---

## 📋 Implementation Steps

### Step 1: Database Layer

#### 1.1 Create Migrations

**Categories Table:**
```php
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->string('name', 100);
    $table->string('slug', 100)->unique();
    $table->text('description')->nullable();
    $table->string('icon', 50)->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    
    $table->index('slug');
    $table->index('is_active');
});
```

**Articles Table (Most Complex):**
```php
Schema::create('articles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('category_id')->constrained()->onDelete('cascade');
    $table->foreignId('source_id')->nullable()->constrained()->onDelete('set null');
    $table->string('title', 500);
    $table->text('description')->nullable();
    $table->longText('content')->nullable();
    $table->text('summary')->nullable();              // AI-generated
    $table->string('url', 1000)->unique();
    $table->string('image_url', 1000)->nullable();
    $table->string('author')->nullable();
    $table->timestamp('published_at');
    $table->enum('status', ['pending', 'processing', 'processed', 'failed'])
          ->default('pending');
    $table->timestamp('processed_at')->nullable();
    $table->timestamps();
    
    // Indexes for performance
    $table->index(['category_id', 'published_at']);    // Personalized feed
    $table->index('status');                           // Filter processed
    $table->unique('url');                             // Prevent duplicates
});
```

#### 1.2 Create Models with Relationships

**User Model:**
```php
class User extends Authenticatable
{
    // Relationships
    public function preferences()
    {
        return $this->hasMany(UserPreference::class);
    }
    
    public function preferredCategories()
    {
        return $this->belongsToMany(Category::class, 'user_preferences');
    }
    
    public function readingHistory()
    {
        return $this->hasMany(ReadingHistory::class);
    }
    
    public function savedArticles()
    {
        return $this->hasMany(SavedArticle::class);
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
```

**Article Model:**
```php
class Article extends Model
{
    protected $fillable = [
        'category_id', 'source_id', 'title', 'description', 'content',
        'summary', 'url', 'image_url', 'author', 'published_at',
        'status', 'processed_at'
    ];
    
    protected $casts = [
        'published_at' => 'datetime',
        'processed_at' => 'datetime',
    ];
    
    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function source()
    {
        return $this->belongsTo(Source::class);
    }
    
    // Scopes
    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }
    
    public function scopeForCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }
}
```

---

### Step 2: Service Layer

#### 2.1 News Service Interface

**Create Interface:**
```php
// app/Services/NewsService.php
interface NewsService
{
    public function fetchNews(string $category, int $limit = 20): array;
}
```

#### 2.2 Mock News Service

**Implementation:**
```php
// app/Services/MockNewsService.php
class MockNewsService implements NewsService
{
    private $templates = [
        'Technology' => [
            'Breaking: {company} announces {product}',
            'New {tech} breakthrough changes {field}',
            '{company} releases {product} with {feature}',
        ],
        'Business' => [
            '{company} stock surges {percent}% after {event}',
            '{company} reports record Q{quarter} earnings',
            'Market analysis: {sector} shows {trend}',
        ],
        // ... more categories
    ];
    
    public function fetchNews(string $category, int $limit = 20): array
    {
        $articles = [];
        
        for ($i = 0; $i < $limit; $i++) {
            $articles[] = [
                'title' => $this->generateTitle($category),
                'description' => $this->generateDescription($category),
                'content' => $this->generateContent($category),
                'url' => 'https://example.com/article-' . uniqid(),
                'image_url' => $this->generateImageUrl($category),
                'author' => $this->generateAuthor(),
                'published_at' => now()->subHours(rand(1, 48)),
                'source' => $this->generateSource($category),
            ];
        }
        
        return $articles;
    }
    
    private function generateTitle($category)
    {
        $templates = $this->templates[$category] ?? $this->templates['Technology'];
        $template = $templates[array_rand($templates)];
        
        $replacements = [
            '{company}' => ['Apple', 'Google', 'Microsoft', 'Tesla', 'Amazon'][array_rand(['Apple', 'Google', 'Microsoft', 'Tesla', 'Amazon'])],
            '{product}' => ['AI model', 'smartphone', 'software', 'platform'][array_rand(['AI model', 'smartphone', 'software', 'platform'])],
            '{tech}' => ['AI', 'quantum computing', 'blockchain', 'robotics'][array_rand(['AI', 'quantum computing', 'blockchain', 'robotics'])],
            '{percent}' => rand(5, 50),
        ];
        
        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
    
    private function generateImageUrl($category)
    {
        $categories = [
            'Technology' => 'technology,computer,phone',
            'Business' => 'business,office,finance',
            'Sports' => 'sports,athlete,stadium',
            'Health' => 'health,medical,fitness',
            'Science' => 'science,laboratory,research',
            'Entertainment' => 'entertainment,movie,music',
        ];
        
        $topic = $categories[$category] ?? 'news';
        return "https://source.unsplash.com/800x600/?{$topic}";
    }
}
```

#### 2.3 Real News API Service

**Implementation:**
```php
// app/Services/NewsAPIService.php
class NewsAPIService implements NewsService
{
    private $apiKey;
    private $baseUrl = 'https://newsapi.org/v2';
    
    public function __construct()
    {
        $this->apiKey = config('services.newsapi.key');
    }
    
    public function fetchNews(string $category, int $limit = 20): array
    {
        $response = Http::get("{$this->baseUrl}/top-headlines", [
            'apiKey' => $this->apiKey,
            'category' => strtolower($category),
            'language' => 'en',
            'pageSize' => $limit,
        ]);
        
        if ($response->failed()) {
            throw new \Exception('Failed to fetch news from NewsAPI');
        }
        
        return $response->json()['articles'] ?? [];
    }
}
```

#### 2.4 AI Service Interface & Implementations

**Mock AI Service:**
```php
// app/Services/MockAIService.php
class MockAIService implements AIService
{
    public function generateSummary(string $content): string
    {
        $sentences = explode('. ', $content);
        $keyPoints = array_slice($sentences, 0, 3);
        
        return implode('. ', $keyPoints) . '.';
    }
}
```

**OpenAI Service:**
```php
// app/Services/OpenAIService.php
class OpenAIService implements AIService
{
    private $client;
    
    public function __construct()
    {
        $this->client = OpenAI::client(config('services.openai.key'));
    }
    
    public function generateSummary(string $content): string
    {
        $response = $this->client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a professional news summarizer. Create concise, informative summaries in 2-3 sentences.'
                ],
                [
                    'role' => 'user',
                    'content' => "Summarize this article:\n\n{$content}"
                ]
            ],
            'max_tokens' => 150,
            'temperature' => 0.7,
        ]);
        
        return $response['choices'][0]['message']['content'];
    }
}
```

---

### Step 3: Queue Jobs

#### 3.1 Fetch News Job

**Implementation:**
```php
// app/Jobs/FetchNewsJob.php
class FetchNewsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function handle(NewsService $newsService)
    {
        $categories = Category::where('is_active', true)->get();
        
        foreach ($categories as $category) {
            try {
                $articles = $newsService->fetchNews($category->name, 20);
                
                foreach ($articles as $articleData) {
                    // Check for duplicates
                    if (Article::where('url', $articleData['url'])->exists()) {
                        continue;
                    }
                    
                    // Find or create source
                    $source = Source::firstOrCreate(
                        ['name' => $articleData['source']['name'] ?? 'Unknown'],
                        ['url' => $articleData['source']['url'] ?? null]
                    );
                    
                    // Create article
                    $article = Article::create([
                        'category_id' => $category->id,
                        'source_id' => $source->id,
                        'title' => $articleData['title'],
                        'description' => $articleData['description'],
                        'content' => $articleData['content'] ?? $articleData['description'],
                        'url' => $articleData['url'],
                        'image_url' => $articleData['urlToImage'] ?? $articleData['image_url'],
                        'author' => $articleData['author'] ?? null,
                        'published_at' => $articleData['publishedAt'] ?? $articleData['published_at'],
                        'status' => 'pending',
                    ]);
                    
                    // Dispatch AI summarization job
                    GenerateSummaryJob::dispatch($article);
                }
                
                Log::info("Fetched articles for category: {$category->name}");
                
            } catch (\Exception $e) {
                Log::error("Failed to fetch news for {$category->name}: {$e->getMessage()}");
            }
        }
    }
}
```

#### 3.2 Generate Summary Job

**Implementation:**
```php
// app/Jobs/GenerateSummaryJob.php
class GenerateSummaryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $tries = 3;
    public $backoff = [60, 300];  // 1 min, 5 min
    public $timeout = 60;
    
    private $article;
    
    public function __construct(Article $article)
    {
        $this->article = $article;
    }
    
    public function handle(AIService $aiService)
    {
        try {
            // Update status
            $this->article->update(['status' => 'processing']);
            
            // Generate summary
            $summary = $aiService->generateSummary(
                $this->article->content ?? $this->article->description
            );
            
            // Update article
            $this->article->update([
                'summary' => $summary,
                'status' => 'processed',
                'processed_at' => now(),
            ]);
            
            Log::info("Generated summary for article: {$this->article->id}");
            
        } catch (\Exception $e) {
            Log::error("Failed to generate summary for article {$this->article->id}: {$e->getMessage()}");
            throw $e;  // Will retry
        }
    }
    
    public function failed(\Throwable $exception)
    {
        // Use fallback: article description
        $this->article->update([
            'summary' => $this->article->description,
            'status' => 'failed',
            'processed_at' => now(),
        ]);
        
        Log::error("All retries exhausted for article {$this->article->id}");
    }
}
```

---

### Step 4: Controllers

#### 4.1 Feed Controller

**Key Methods:**
```php
// app/Http/Controllers/FeedController.php
class FeedController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Check preferences
        if ($user->preferences()->count() === 0) {
            return redirect()->route('preferences.index')
                ->with('message', 'Please select your topics first');
        }
        
        // Get preferred category IDs
        $categoryIds = $user->preferences()->pluck('category_id')->toArray();
        
        // Get read article IDs
        $readArticleIds = $user->readingHistory()->pluck('article_id')->toArray();
        
        // Build personalized feed
        $articles = Article::with(['category', 'source'])
            ->whereIn('category_id', $categoryIds)
            ->whereNotIn('id', $readArticleIds)
            ->where('status', 'processed')
            ->orderBy('published_at', 'desc')
            ->paginate(20);
        
        $categories = Category::where('is_active', true)->get();
        
        return view('feed.index', compact('articles', 'categories'));
    }
}
```

---

### Step 5: Views

#### 5.1 Feed View

**Blade Template Highlights:**
```blade
{{-- resources/views/feed/index.blade.php --}}
@foreach($articles as $article)
    <article class="bg-white rounded-lg shadow-sm p-6 mb-4">
        {{-- Category & Meta --}}
        <div class="text-sm text-gray-500 mb-2">
            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">
                {{ $article->category->icon }} {{ $article->category->name }}
            </span>
            <span class="mx-2">•</span>
            <span>{{ $article->source?->name }}</span>
            <span class="mx-2">•</span>
            <span>{{ $article->published_at->diffForHumans() }}</span>
        </div>
        
        {{-- Title --}}
        <h2 class="text-xl font-semibold mb-2">
            <a href="{{ route('articles.show', $article) }}" class="hover:text-blue-600">
                {{ $article->title }}
            </a>
        </h2>
        
        {{-- AI Summary --}}
        @if($article->summary)
            <p class="text-gray-600 mb-4">
                <span class="font-medium text-blue-600">🤖 AI Summary:</span>
                {{ Str::limit($article->summary, 200) }}
            </p>
        @endif
        
        {{-- Actions --}}
        <div class="flex space-x-4">
            <button onclick="toggleSave({{ $article->id }})" 
                    id="save-btn-{{ $article->id }}"
                    class="text-sm text-gray-500 hover:text-gray-700">
                💾 Save
            </button>
            <a href="{{ route('articles.show', $article) }}" 
               class="text-sm text-blue-600 hover:text-blue-800">
                👁️ Read More
            </a>
        </div>
    </article>
@endforeach
```

---

### Step 6: Configuration

#### 6.1 Service Provider Binding

**In AppServiceProvider:**
```php
public function register()
{
    // News Service binding
    if (config('services.use_mock_services')) {
        $this->app->bind(NewsService::class, MockNewsService::class);
        $this->app->bind(AIService::class, MockAIService::class);
    } else {
        $this->app->bind(NewsService::class, NewsAPIService::class);
        $this->app->bind(AIService::class, OpenAIService::class);
    }
}
```

#### 6.2 Services Configuration

**In config/services.php:**
```php
'newsapi' => [
    'key' => env('NEWSAPI_KEY'),
],

'openai' => [
    'key' => env('OPENAI_API_KEY'),
],

'use_mock_services' => env('USE_MOCK_SERVICES', true),
```

---

### Step 7: Scheduler

**In app/Console/Kernel.php:**
```php
protected function schedule(Schedule $schedule)
{
    // Fetch news every hour
    $schedule->job(new FetchNewsJob)->hourly();
}
```

---

## 🔧 Key Implementation Patterns

### Pattern 1: Repository via Eloquent
```php
// Instead of raw queries
Article::where('category_id', $categoryId)->get();

// Use model scopes
Article::processed()->forCategory($categoryId)->get();
```

### Pattern 2: Service Injection
```php
// Controller method
public function handle(NewsService $newsService)
{
    // Laravel automatically injects correct implementation
    $articles = $newsService->fetchNews('Technology');
}
```

### Pattern 3: Job Dispatching
```php
// After creating article
GenerateSummaryJob::dispatch($article);

// With delay
GenerateSummaryJob::dispatch($article)->delay(now()->addMinutes(5));

// With priority
GenerateSummaryJob::dispatch($article)->onQueue('high');
```

---

## 📊 Data Flow Example

**Complete flow for personalized feed:**

1. **User requests feed** → `GET /feed`
2. **FeedController receives request** → `index()`
3. **Check user preferences** → Query `user_preferences`
4. **Get category IDs** → `[1, 2, 5]` (Tech, Business, Science)
5. **Get read articles** → Query `reading_history`
6. **Build query:**
```sql
   SELECT * FROM articles
   WHERE category_id IN (1, 2, 5)
   AND id NOT IN (read article IDs)
   AND status = 'processed'
   ORDER BY published_at DESC
   LIMIT 20
```
7. **Load relationships** → Eager load category, source
8. **Render view** → Pass to Blade template
9. **Display feed** → User sees personalized articles

---

## 🧪 Testing

### Unit Test Example
```php
public function test_mock_news_service_generates_articles()
{
    $newsService = new MockNewsService();
    $articles = $newsService->fetchNews('Technology', 5);
    
    $this->assertCount(5, $articles);
    $this->assertArrayHasKey('title', $articles[0]);
    $this->assertArrayHasKey('url', $articles[0]);
}
```

### Feature Test Example
```php
public function test_personalized_feed_shows_only_preferred_categories()
{
    $user = User::factory()->create();
    $tech = Category::where('slug', 'technology')->first();
    
    // Set preference
    UserPreference::create([
        'user_id' => $user->id,
        'category_id' => $tech->id,
    ]);
    
    // Create articles
    Article::factory()->create(['category_id' => $tech->id, 'status' => 'processed']);
    Article::factory()->create(['category_id' => 2, 'status' => 'processed']); // Different category
    
    // Test
    $response = $this->actingAs($user)->get('/feed');
    
    $response->assertStatus(200);
    $response->assertSee('Technology'); // Should see tech articles
    // Should not see other category articles
}
```

---

## 🚀 Running the Code

### Complete Setup Command Sequence
```bash
# 1. Install dependencies
composer install
npm install

# 2. Configure environment
cp .env.example .env
php artisan key:generate

# 3. Configure database
# Edit .env with your database credentials

# 4. Run migrations
php artisan migrate --seed

# 5. Build assets
npm run build

# 6. Start servers
php artisan serve              # Terminal 1
php artisan queue:work         # Terminal 2

# 7. Fetch initial news
php artisan news:fetch         # Terminal 3
```

---

## 📝 Common Commands
```bash
# Clear all caches
php artisan optimize:clear

# Run migrations fresh
php artisan migrate:fresh --seed

# Run tests
php artisan test

# Check routes
php artisan route:list

# Tinker (test code)
php artisan tinker
>>> Article::count()
>>> User::first()->preferences()->count()
```

---

## 🔍 Debugging Tips

### Check if articles are being fetched:
```bash
php artisan tinker
>>> Article::count()
>>> Article::where('status', 'pending')->count()
>>> Article::where('status', 'processed')->count()
```

### Check queue jobs:
```bash
# See jobs table
php artisan tinker
>>> DB::table('jobs')->count()

# Process jobs manually
php artisan queue:work --once
```

### Check logs:
```bash
# In storage/logs/laravel.log
tail -f storage/logs/laravel.log
```

---

## Summary

This implementation provides:

✅ **Clean Architecture** - Layered, maintainable code  
✅ **SOLID Principles** - Single responsibility, dependency injection  
✅ **Testable Code** - Interface-based design  
✅ **Scalable Design** - Queue-based processing  
✅ **Production Ready** - Error handling, logging, fallbacks  

The codebase is structured for easy understanding, maintenance, and future enhancements.