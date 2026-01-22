# Database Schema & Design

## Overview

This document provides a comprehensive overview of the database design, including all tables, relationships, indexes, and constraints.

---

## 1. Entity Relationship Diagram

![Database Schema](images/04-database-schema.png)

*Figure 3.1: Complete database schema with all tables and relationships*

---

## 2. Database Overview

### Statistics
- **Total Tables**: 8
- **Core Tables**: 4 (users, categories, sources, articles)
- **Relationship Tables**: 3 (user_preferences, reading_history, saved_articles)
- **Supporting Tables**: 1 (article_tags - optional)

### Database Engine
- **Engine**: InnoDB
- **Character Set**: utf8mb4
- **Collation**: utf8mb4_unicode_ci
- **Why?**: Full Unicode support (including emojis), ACID compliance, foreign key support

---

## 3. Table Schemas

### Table 1: users

**Purpose**: Store user accounts and authentication data
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Key Fields:**
- `id` - Primary key, auto-increment
- `email` - Unique, used for login
- `password` - bcrypt hashed
- `remember_token` - For "Remember Me" functionality

**Relationships:**
- Has many `user_preferences`
- Has many `reading_history` records
- Has many `saved_articles`

---

### Table 2: categories

**Purpose**: Store news categories/topics for classification
```sql
CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    icon VARCHAR(50) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_slug (slug),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Seeded Categories:**
1. Technology 💻
2. Business 💼
3. Sports ⚽
4. Health 🏥
5. Science 🔬
6. Entertainment 🎬

**Key Fields:**
- `slug` - URL-friendly identifier
- `icon` - Emoji representation
- `is_active` - Soft enable/disable

**Relationships:**
- Has many `articles`
- Has many `user_preferences`

---

### Table 3: sources

**Purpose**: Store news publishers/sources
```sql
CREATE TABLE sources (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    url VARCHAR(500) NULL,
    logo_url VARCHAR(500) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Example Sources:**
- TechCrunch
- BBC News
- ESPN
- Reuters
- The Verge

**Key Fields:**
- `name` - Publisher name
- `url` - Publisher website
- `logo_url` - Publisher logo for display

**Relationships:**
- Has many `articles`

---

### Table 4: articles

**Purpose**: Store news articles with AI-generated summaries
```sql
CREATE TABLE articles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id BIGINT UNSIGNED NOT NULL,
    source_id BIGINT UNSIGNED NULL,
    title VARCHAR(500) NOT NULL,
    description TEXT NULL,
    content LONGTEXT NULL,
    summary TEXT NULL,
    url VARCHAR(1000) NOT NULL UNIQUE,
    image_url VARCHAR(1000) NULL,
    author VARCHAR(255) NULL,
    published_at TIMESTAMP NOT NULL,
    status ENUM('pending', 'processing', 'processed', 'failed') DEFAULT 'pending',
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (source_id) REFERENCES sources(id) ON DELETE SET NULL,
    
    INDEX idx_category_published (category_id, published_at),
    INDEX idx_status (status),
    INDEX idx_published_at (published_at),
    UNIQUE INDEX idx_url (url)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Status Values:**
- `pending` - Waiting for AI summary
- `processing` - AI summary in progress
- `processed` - AI summary completed
- `failed` - AI failed, using fallback

**Key Fields:**
- `summary` - AI-generated summary (150-200 words)
- `content` - Full article text
- `url` - Unique, prevents duplicates
- `status` - Tracks AI processing

**Relationships:**
- Belongs to `category`
- Belongs to `source`
- Has many `reading_history` records
- Has many `saved_articles`

**Important Indexes:**
- `idx_category_published` - Fast personalized feed queries
- `idx_status` - Quick filtering of processed articles
- `idx_url` - Duplicate detection

---

### Table 5: user_preferences

**Purpose**: Link users to their preferred categories
```sql
CREATE TABLE user_preferences (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    
    UNIQUE INDEX idx_user_category (user_id, category_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Purpose:**
- Store which topics each user is interested in
- Used for personalized feed generation
- User can have multiple preferences

**Example Data:**
```
user_id | category_id
--------|------------
1       | 1          (User 1 likes Technology)
1       | 2          (User 1 likes Business)
1       | 5          (User 1 likes Science)
```

**Key Constraints:**
- `UNIQUE (user_id, category_id)` - Prevent duplicate preferences
- `ON DELETE CASCADE` - Remove preferences when user/category deleted

---

### Table 6: reading_history

**Purpose**: Track which articles users have read
```sql
CREATE TABLE reading_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    article_id BIGINT UNSIGNED NOT NULL,
    read_at TIMESTAMP NOT NULL,
    time_spent INT DEFAULT 0 COMMENT 'Time in seconds',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    
    UNIQUE INDEX idx_user_article (user_id, article_id),
    INDEX idx_user_id (user_id),
    INDEX idx_read_at (read_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Purpose:**
- Prevent showing read articles again in feed
- Track reading engagement (time spent)
- Analytics for future recommendations

**Key Fields:**
- `read_at` - When article was read
- `time_spent` - How long user spent reading (seconds)

**Usage:**
```php
// Exclude read articles from feed
Article::whereNotIn('id', function($query) use ($userId) {
    $query->select('article_id')
          ->from('reading_history')
          ->where('user_id', $userId);
});
```

---

### Table 7: saved_articles

**Purpose**: Store user bookmarks/saved articles
```sql
CREATE TABLE saved_articles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    article_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    
    UNIQUE INDEX idx_user_article (user_id, article_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Purpose:**
- Allow users to bookmark articles for later
- Quick access to saved content
- Reading list functionality

**Key Constraints:**
- `UNIQUE (user_id, article_id)` - Can't save same article twice

---

### Table 8: article_tags (Optional)

**Purpose**: Additional tagging/categorization
```sql
CREATE TABLE article_tags (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    article_id BIGINT UNSIGNED NOT NULL,
    tag VARCHAR(100) NOT NULL,
    created_at TIMESTAMP NULL,
    
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    
    INDEX idx_article_id (article_id),
    INDEX idx_tag (tag)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Purpose:**
- Optional feature for future enhancements
- Multiple tags per article
- Better categorization

---

## 4. Relationships Diagram
```
users (1) ─────< (N) user_preferences (N) >───── (1) categories
  │                                                      │
  │                                                      │
  ├──< (N) reading_history (N) >───────┐                │
  │                                     │                │
  └──< (N) saved_articles (N) >────────┤                │
                                        │                │
                                        ▼                ▼
                                    articles (N) >───── (1) sources
                                        │
                                        └──< (N) article_tags (Optional)
```

### Relationship Types:

**One-to-Many:**
- User → User Preferences
- User → Reading History
- User → Saved Articles
- Category → Articles
- Source → Articles
- Article → Article Tags

**Many-to-Many (through pivot):**
- Users ↔ Categories (through user_preferences)
- Users ↔ Articles (through reading_history)
- Users ↔ Articles (through saved_articles)

---

## 5. Indexes Strategy

### Primary Indexes (All Tables)
- Every table has `id` as PRIMARY KEY
- Auto-increment for performance
- Used for all relationships

### Foreign Key Indexes
```sql
-- Automatically indexed by foreign key constraint
user_preferences.user_id
user_preferences.category_id
reading_history.user_id
reading_history.article_id
saved_articles.user_id
saved_articles.article_id
articles.category_id
articles.source_id
```

### Composite Indexes
```sql
-- For personalized feed query
INDEX idx_category_published ON articles(category_id, published_at);

-- Prevent duplicate preferences
UNIQUE INDEX idx_user_category ON user_preferences(user_id, category_id);

-- Prevent duplicate reading history
UNIQUE INDEX idx_user_article ON reading_history(user_id, article_id);
```

### Query-Specific Indexes
```sql
-- For filtering processed articles
INDEX idx_status ON articles(status);

-- For duplicate detection
UNIQUE INDEX idx_url ON articles(url);

-- For active categories
INDEX idx_is_active ON categories(is_active);
```

---

## 6. Sample Queries

### Query 1: Get Personalized Feed
```sql
SELECT articles.*
FROM articles
INNER JOIN user_preferences ON articles.category_id = user_preferences.category_id
WHERE user_preferences.user_id = 1
  AND articles.status = 'processed'
  AND articles.id NOT IN (
      SELECT article_id 
      FROM reading_history 
      WHERE user_id = 1
  )
ORDER BY articles.published_at DESC
LIMIT 20;
```

**Indexes Used:**
- `user_preferences.idx_user_id`
- `articles.idx_category_published`
- `articles.idx_status`
- `reading_history.idx_user_id`

---

### Query 2: Get User's Saved Articles
```sql
SELECT articles.*
FROM articles
INNER JOIN saved_articles ON articles.id = saved_articles.article_id
WHERE saved_articles.user_id = 1
ORDER BY saved_articles.created_at DESC;
```

**Indexes Used:**
- `saved_articles.idx_user_id`
- `articles.id` (PRIMARY)

---

### Query 3: Get Trending Articles (Most Read)
```sql
SELECT articles.*, COUNT(reading_history.id) as read_count
FROM articles
LEFT JOIN reading_history ON articles.id = reading_history.article_id
WHERE articles.published_at >= NOW() - INTERVAL 7 DAY
  AND articles.status = 'processed'
GROUP BY articles.id
ORDER BY read_count DESC
LIMIT 10;
```

**Indexes Used:**
- `articles.idx_published_at`
- `articles.idx_status`
- `reading_history.idx_article_id`

---

## 7. Data Integrity

### Foreign Key Constraints

**CASCADE DELETE:**
```sql
-- When user is deleted, delete all their data
user_preferences.user_id → users.id (ON DELETE CASCADE)
reading_history.user_id → users.id (ON DELETE CASCADE)
saved_articles.user_id → users.id (ON DELETE CASCADE)

-- When category is deleted, delete articles
articles.category_id → categories.id (ON DELETE CASCADE)
```

**SET NULL:**
```sql
-- When source is deleted, keep articles but set source_id to NULL
articles.source_id → sources.id (ON DELETE SET NULL)
```

### Unique Constraints

**Prevent Duplicates:**
```sql
-- Same email can't be used twice
users.email (UNIQUE)

-- Same article URL can't be added twice
articles.url (UNIQUE)

-- User can't prefer same category twice
user_preferences(user_id, category_id) (UNIQUE)

-- User can't have duplicate reading history
reading_history(user_id, article_id) (UNIQUE)
```

---

## 8. Performance Considerations

### Query Optimization

**Use Indexes:**
✅ All foreign keys indexed  
✅ Composite indexes for common queries  
✅ Covering indexes for frequent lookups  

**Avoid:**
❌ SELECT * (use specific columns)  
❌ N+1 queries (use eager loading)  
❌ Queries without WHERE on large tables  

### Partitioning Strategy (Future)

**When to Partition:**
- Articles table > 10 million rows
- Reading history > 50 million rows

**Partition by:**
```sql
-- Partition articles by month
PARTITION BY RANGE (YEAR(published_at)) (
    PARTITION p2024 VALUES LESS THAN (2025),
    PARTITION p2025 VALUES LESS THAN (2026),
    PARTITION p2026 VALUES LESS THAN (2027)
);
```

---

## 9. Backup & Maintenance

### Backup Strategy

**Daily Backups:**
```bash
mysqldump -u root -p news_feed > backup_$(date +%Y%m%d).sql
```

**What to Backup:**
- All tables
- Stored procedures (if any)
- Triggers (if any)

**Retention:**
- Daily: Keep 7 days
- Weekly: Keep 4 weeks
- Monthly: Keep 12 months

### Maintenance Tasks

**Weekly:**
```sql
-- Analyze tables for query optimization
ANALYZE TABLE articles, reading_history, saved_articles;

-- Optimize tables (defragment)
OPTIMIZE TABLE articles;
```

**Monthly:**
```sql
-- Clean old reading history (> 1 year)
DELETE FROM reading_history 
WHERE created_at < NOW() - INTERVAL 1 YEAR;

-- Archive old articles
-- Move articles older than 6 months to archive table
```

---

## 10. Scaling Considerations

### Horizontal Scaling

**Read Replicas:**
- Master for writes
- Slaves for reads (feed queries)
- Reduces load on primary database

**Sharding Strategy:**
```
Shard 1: users with id 1-1000000
Shard 2: users with id 1000001-2000000
Shard 3: users with id 2000001-3000000
```

### Vertical Scaling

**Hardware Upgrades:**
- More RAM (for indexes in memory)
- Faster SSD (for I/O operations)
- More CPU cores (for concurrent queries)

### Caching Layer

**Redis Cache:**
```php
// Cache user preferences
Cache::remember("user:{$userId}:preferences", 3600, function() {
    return UserPreference::where('user_id', $userId)->get();
});

// Cache processed articles
Cache::remember("articles:processed", 300, function() {
    return Article::where('status', 'processed')
                  ->latest()
                  ->limit(100)
                  ->get();
});
```

---

## Summary

### Database Characteristics

**Size Estimates:**
- POC: < 1 GB
- 10K users, 100K articles: ~5 GB
- 100K users, 1M articles: ~50 GB
- 1M users, 10M articles: ~500 GB

**Performance:**
- Feed query: < 100ms (with indexes)
- Insert article: < 10ms
- Update with summary: < 5ms

**Scalability:**
- Current: Handles 10K-100K users
- With optimization: 100K-1M users
- With sharding: 1M+ users

**Data Integrity:**
✅ Foreign key constraints  
✅ Unique constraints  
✅ Proper indexing  
✅ CASCADE rules  
✅ Transaction support (InnoDB)  

This schema provides a solid foundation for growth from POC to production scale.