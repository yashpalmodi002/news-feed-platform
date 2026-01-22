# AI/Data Pipeline Architecture

## News Ingestion & Processing Pipeline

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    STEP 1: NEWS INGESTION                                │
└─────────────────────────────────────────────────────────────────────────┘

    ┌──────────────────┐
    │  Scheduler (Cron) │  (Runs every hour)
    └────────┬──────────┘
             │
             ▼
    ┌──────────────────────┐
    │  FetchNewsCommand    │  (Artisan Command)
    └──────────┬───────────┘
               │
               ▼
    ┌──────────────────────────────┐
    │   NewsAPIService             │
    │  - Fetch by categories       │
    │  - Filter duplicates         │
    │  - Validate data             │
    └──────────┬───────────────────┘
               │
               │  Raw Article Data
               ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                    STEP 2: DATA VALIDATION & STORAGE                     │
└─────────────────────────────────────────────────────────────────────────┘

    ┌──────────────────────────────┐
    │  ArticleRepository           │
    │  - Check for duplicates      │
    │  - Validate required fields  │
    │  - Extract metadata          │
    └──────────┬───────────────────┘
               │
               ▼
    ┌──────────────────────────────┐
    │  Store Raw Article           │
    │  (without summary)           │
    │  Status: 'pending'           │
    └──────────┬───────────────────┘
               │
               │  Dispatch Job
               ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                    STEP 3: AI SUMMARIZATION (Queued)                     │
└─────────────────────────────────────────────────────────────────────────┘

    ┌──────────────────────────────┐
    │  Queue: default              │
    │  Job: GenerateSummaryJob     │
    └──────────┬───────────────────┘
               │
               ▼
    ┌──────────────────────────────┐
    │  AI Service (OpenAI)         │
    │                              │
    │  Input:                      │
    │  - Article title             │
    │  - Article content/desc      │
    │  - Target length: 2-3 lines  │
    │                              │
    │  API Call:                   │
    │  POST /v1/chat/completions   │
    │  Model: gpt-3.5-turbo        │
    └──────────┬───────────────────┘
               │
               │  AI Generated Summary
               ▼
    ┌──────────────────────────────┐
    │  Update Article              │
    │  - summary field             │
    │  - status: 'processed'       │
    │  - processed_at timestamp    │
    └──────────┬───────────────────┘
               │
               ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                    STEP 4: PERSONALIZATION ENGINE                        │
└─────────────────────────────────────────────────────────────────────────┘

    ┌──────────────────────────────┐
    │  User Requests Feed          │
    └──────────┬───────────────────┘
               │
               ▼
    ┌──────────────────────────────────────┐
    │  FeedService                         │
    │                                      │
    │  1. Get user preferences (topics)    │
    │  2. Get reading history              │
    │  3. Query articles:                  │
    │     - Match categories               │
    │     - Exclude already read           │
    │     - Order by published date        │
    │     - Limit results                  │
    │                                      │
    │  Optional Enhancement:               │
    │  - Recommend similar articles        │
    │  - Based on reading patterns         │
    └──────────┬───────────────────────────┘
               │
               │  Personalized Articles
               ▼
    ┌──────────────────────────────┐
    │  Return to User              │
    │  - Articles with summaries   │
    │  - Sorted by relevance       │
    └──────────────────────────────┘
```

## Data Flow Details

### 1. News Fetching
- **Trigger**: Laravel Scheduler (runs hourly)
- **Source**: NewsAPI.org (free tier: 100 requests/day)
- **Categories**: Technology, Business, Sports, Entertainment, Health, Science
- **Data Retrieved**: Title, Description, Content, URL, Image, Published Date, Source

### 2. AI Summarization
- **When**: After article is stored in database
- **How**: Background job (Laravel Queue)
- **AI Provider**: OpenAI API (gpt-3.5-turbo)
- **Prompt Template**:
  ```
  Summarize the following news article in 2-3 concise sentences:
  
  Title: {article_title}
  Content: {article_content}
  
  Summary:
  ```
- **Fallback**: If API fails, use article description as summary

### 3. Personalization Logic
```php
// Pseudo-code
function getPersonalizedFeed($userId) {
    $userCategories = getUserPreferences($userId);
    $readArticles = getReadingHistory($userId);
    
    $articles = Article::query()
        ->whereIn('category_id', $userCategories)
        ->whereNotIn('id', $readArticles)
        ->where('status', 'processed')
        ->orderBy('published_at', 'desc')
        ->limit(20)
        ->get();
    
    return $articles;
}
```

## Error Handling

1. **News API Failure**:
   - Retry mechanism (3 attempts)
   - Log error
   - Use cached data if available

2. **AI API Failure**:
   - Queue job for retry (max 3 attempts)
   - Fallback to article description
   - Mark as 'partial' status

3. **Database Errors**:
   - Transaction rollback
   - Log error
   - Notify admin

## Performance Optimization

1. **Batch Processing**: Process multiple articles in single API call
2. **Caching**: Cache user preferences and popular articles
3. **Indexing**: Database indexes on category_id, published_at, status
4. **Queue Priority**: High priority for user-requested summaries
5. **Rate Limiting**: Respect API rate limits with exponential backoff

## Cost Considerations

- **NewsAPI**: Free tier (100 requests/day) or $449/month for unlimited
- **OpenAI**: ~$0.002 per summary (1000 tokens)
- **Alternative**: Use open-source models (Hugging Face) for cost reduction

## Future Enhancements

1. **Recommendation Engine**: ML-based article recommendations
2. **Sentiment Analysis**: Tag articles with sentiment
3. **Topic Extraction**: Auto-tag articles with relevant topics
4. **Multi-language Support**: Translate summaries
5. **Custom AI Models**: Fine-tune models for better summaries
