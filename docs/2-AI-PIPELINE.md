# AI/Data Pipeline Architecture

## Overview

This document details the complete AI processing pipeline, from news fetching through AI summarization, including data flow, error handling, and fallback mechanisms.

---

## 1. AI Pipeline Flow

![AI Pipeline Flow](images/05-ai-pipeline-flow.png)

*Figure 2.1: End-to-end AI pipeline showing news fetching, validation, storage, and AI summarization*

---

## 2. Pipeline Stages

### Stage 1: News Fetching

#### Trigger Mechanism
```php
// In app/Console/Kernel.php
$schedule->job(new FetchNewsJob)->hourly();
```

#### Process Flow
1. **Laravel Scheduler** runs every hour
2. Checks if it's time to fetch news
3. Dispatches `FetchNewsJob` to queue
4. Job is added to `jobs` table

#### NewsService Selection
```php
if (config('services.use_mock_services')) {
    $newsService = new MockNewsService();
} else {
    $newsService = new NewsAPIService();
}
```

---

### Stage 2: Data Validation & Deduplication

#### Validation Rules
```php
$validatedData = [
    'title' => required, max:500
    'description' => required
    'url' => required, unique, valid URL
    'published_at' => required, valid date
    'category' => required, exists in categories
];
```

#### Duplicate Check
```php
// Check if article already exists by URL
$exists = Article::where('url', $articleUrl)->exists();

if ($exists) {
    // Skip this article
    continue;
}
```

---

### Stage 3: Article Storage

#### Initial Storage
```php
Article::create([
    'title' => $data['title'],
    'description' => $data['description'],
    'content' => $data['content'],
    'url' => $data['url'],
    'image_url' => $data['image_url'],
    'category_id' => $categoryId,
    'source_id' => $sourceId,
    'published_at' => $data['published_at'],
    'status' => 'pending',  // Important!
    'summary' => null        // Will be filled by AI
]);
```

#### Status States
- `pending` - Article stored, waiting for AI summary
- `processing` - AI summary generation in progress
- `processed` - AI summary completed successfully
- `failed` - AI summarization failed (using fallback)

---

### Stage 4: AI Summarization

#### Job Dispatch
```php
// After storing article
GenerateSummaryJob::dispatch($article);
```

#### AI Service Selection
```php
if (config('services.use_mock_services')) {
    $aiService = new MockAIService();
} else {
    $aiService = new OpenAIService();
}
```

#### OpenAI API Call
```php
$response = Http::withHeaders([
    'Authorization' => 'Bearer ' . config('services.openai.key'),
])
->timeout(30)
->post('https://api.openai.com/v1/chat/completions', [
    'model' => 'gpt-3.5-turbo',
    'messages' => [
        [
            'role' => 'system',
            'content' => 'You are a news summarizer. Create concise, informative summaries.'
        ],
        [
            'role' => 'user',
            'content' => "Summarize this article in 2-3 sentences:\n\n{$article->content}"
        ]
    ],
    'max_tokens' => 150,
    'temperature' => 0.7,
]);

$summary = $response['choices'][0]['message']['content'];
```

---

### Stage 5: Article Update

#### Success Path
```php
$article->update([
    'summary' => $summary,
    'status' => 'processed',
    'processed_at' => now(),
]);
```

#### Failure Path (Fallback)
```php
// If AI fails, use article description
$article->update([
    'summary' => $article->description,
    'status' => 'failed',
    'processed_at' => now(),
]);
```

---

## 3. Data Flow Sequence

![Data Flow Sequence](images/03-data-flow-sequence.png)

*Figure 2.2: Detailed sequence diagram showing all system interactions*

### Complete Flow Breakdown

#### User Registration Flow
```
User → Browser → POST /register
Browser → AuthController → Create User
AuthController → User Model → INSERT users table
User Model → Database → Return user ID
Database → User Model → User object
User Model → AuthController → Login user
AuthController → Browser → Redirect to /preferences
```

#### Topic Selection Flow
```
User → Browser → Select topics
Browser → PreferenceController → POST /preferences
PreferenceController → Validate input
PreferenceController → UserPreference Model
UserPreference Model → Database → INSERT user_preferences
Database → PreferenceController → Success
PreferenceController → Browser → Redirect to /feed
```

#### Background News Fetching
```
Scheduler → FetchNewsJob → Queue
Queue Worker → FetchNewsJob → execute()
FetchNewsJob → NewsService → fetchNews()
NewsService → External API / Mock → Articles data
NewsService → FetchNewsJob → Array of articles
FetchNewsJob → Article Model → Store articles
Article Model → Database → INSERT articles (status=pending)
FetchNewsJob → Dispatch GenerateSummaryJob → For each article
```

#### AI Summarization
```
Queue Worker → GenerateSummaryJob → execute()
GenerateSummaryJob → AIService → generateSummary()
AIService → OpenAI API / Mock → API call
OpenAI API → AIService → Summary text
AIService → GenerateSummaryJob → Return summary
GenerateSummaryJob → Article Model → Update article
Article Model → Database → UPDATE articles (summary, status=processed)
```

#### Feed Display
```
User → Browser → GET /feed
Browser → FeedController → index()
FeedController → UserPreference → Get user's categories
UserPreference → Database → SELECT preferences
FeedController → ReadingHistory → Get read article IDs
ReadingHistory → Database → SELECT read articles
FeedController → Article Model → Query personalized feed
Article Model → Database → SELECT articles WHERE category IN (...) AND id NOT IN (...)
Database → Article Model → Article collection
Article Model → FeedController → Paginated articles
FeedController → Blade View → Render feed
Blade View → Browser → HTML response
Browser → User → Display feed
```

---

## 4. Mock vs Real Services

### Mock Services (Development)

#### MockNewsService
```php
public function fetchNews($category, $limit = 20)
{
    $templates = [
        'Technology' => [
            'Breaking: New AI breakthrough in {tech}',
            '{company} announces revolutionary {product}',
        ],
        // ... more templates
    ];
    
    // Generate fake but realistic articles
    return $this->generateArticles($templates, $category, $limit);
}
```

**Advantages:**
- ✅ No API costs
- ✅ Instant results
- ✅ No rate limits
- ✅ Consistent test data
- ✅ Works offline

#### MockAIService
```php
public function generateSummary($content)
{
    // Extract key sentences
    // Create template-based summary
    return "This article discusses {topic} and highlights {key_point}. 
            It concludes that {conclusion}.";
}
```

**Advantages:**
- ✅ Instant summarization
- ✅ No API costs
- ✅ Predictable output
- ✅ Good for testing

---

### Real Services (Production)

#### NewsAPIService
```php
public function fetchNews($category, $limit = 20)
{
    $response = Http::get('https://newsapi.org/v2/top-headlines', [
        'apiKey' => config('services.newsapi.key'),
        'category' => strtolower($category),
        'language' => 'en',
        'pageSize' => $limit,
    ]);
    
    return $response['articles'];
}
```

**Features:**
- ✅ Real news from 80,000+ sources
- ✅ Up-to-date content
- ✅ Multiple categories
- ✅ Reliable API

**Costs:**
- Free tier: 100 requests/day
- Paid tier: $449/month for unlimited

#### OpenAIService
```php
public function generateSummary($content)
{
    $response = $this->client->chat()->create([
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role' => 'system', 'content' => 'Summarize news articles'],
            ['role' => 'user', 'content' => $content],
        ],
    ]);
    
    return $response['choices'][0]['message']['content'];
}
```

**Features:**
- ✅ High-quality summaries
- ✅ Natural language
- ✅ Contextual understanding
- ✅ Reliable API

**Costs:**
- ~$0.002 per summary
- ~$20-50/month for 10,000 articles

---

## 5. Error Handling & Retry Logic

### Job Retry Configuration
```php
class GenerateSummaryJob implements ShouldQueue
{
    public $tries = 3;              // Retry 3 times
    public $backoff = [60, 300];    // Wait 1min, then 5min
    public $timeout = 60;           // 60 second timeout
}
```

### Error Scenarios & Handling

#### Scenario 1: API Timeout
```php
try {
    $summary = $aiService->generateSummary($article->content);
} catch (RequestException $e) {
    if ($e->getCode() === 408) {
        // Timeout - will retry automatically
        throw $e;
    }
}
```

#### Scenario 2: API Rate Limit
```php
if ($response->status() === 429) {
    // Rate limited
    $this->release(3600); // Retry in 1 hour
    return;
}
```

#### Scenario 3: Invalid API Key
```php
if ($response->status() === 401) {
    // Invalid credentials - don't retry
    Log::error('Invalid API key');
    $this->fail('Invalid API credentials');
    return;
}
```

#### Scenario 4: All Retries Exhausted
```php
public function failed(Throwable $exception)
{
    // Use fallback: article description
    $this->article->update([
        'summary' => $this->article->description,
        'status' => 'failed',
        'processed_at' => now(),
    ]);
    
    Log::error("AI summary failed for article {$this->article->id}");
}
```

---

## 6. Performance Optimization

### Batch Processing
```php
// Process 10 articles at once
$articles = Article::where('status', 'pending')
    ->limit(10)
    ->get();

foreach ($articles as $article) {
    GenerateSummaryJob::dispatch($article);
}
```

### Caching Strategy
```php
// Cache summaries for similar content
$cacheKey = 'summary:' . md5($article->content);

$summary = Cache::remember($cacheKey, 86400, function () use ($article) {
    return $this->aiService->generateSummary($article->content);
});
```

### Queue Prioritization
```php
// High priority for breaking news
if ($article->category->name === 'Breaking News') {
    GenerateSummaryJob::dispatch($article)->onQueue('high');
} else {
    GenerateSummaryJob::dispatch($article)->onQueue('default');
}
```

---

## 7. Monitoring & Logging

### Key Metrics to Track

#### Performance Metrics
- News fetch time (should be < 5 seconds)
- AI summary generation time (should be < 10 seconds)
- Queue processing rate (articles per minute)
- Failed job percentage (should be < 5%)

#### Usage Metrics
- Total articles fetched per day
- Total summaries generated per day
- API call success rate
- Cache hit rate

### Logging Examples
```php
// In FetchNewsJob
Log::info("Fetched {$count} articles for category: {$category}");

// In GenerateSummaryJob
Log::info("Generated summary for article: {$article->id}");

// On error
Log::error("Failed to generate summary", [
    'article_id' => $article->id,
    'error' => $exception->getMessage(),
]);
```

---

## 8. Data Pipeline Summary

### Pipeline Characteristics

**Throughput:**
- Can process 100+ articles per hour
- Limited by external API rate limits
- Horizontally scalable with more workers

**Latency:**
- News fetch: 2-5 seconds
- AI summary: 3-10 seconds
- Total: 5-15 seconds per article

**Reliability:**
- Automatic retries on failure
- Fallback mechanisms
- Error tracking and logging
- 95%+ success rate

**Cost Efficiency:**
- Free tier available (100 articles/day)
- Production cost: ~$50-100/month for 10K articles
- Scales linearly with usage

---

## Summary

The AI/Data pipeline provides:
✅ **Automated News Fetching** - Hourly updates  
✅ **AI-Powered Summaries** - High-quality, concise  
✅ **Error Resilience** - Retry and fallback mechanisms  
✅ **Cost Efficiency** - Mock services for development  
✅ **Scalability** - Queue-based processing  
✅ **Monitoring** - Comprehensive logging  

This pipeline can handle growth from POC to production scale.