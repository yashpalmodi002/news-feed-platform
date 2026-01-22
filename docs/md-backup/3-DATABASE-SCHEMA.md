# Database Schema Design

## Entity Relationship Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│                        DATABASE SCHEMA                               │
└─────────────────────────────────────────────────────────────────────┘

┌──────────────────────┐
│       users          │
├──────────────────────┤
│ id (PK)              │──┐
│ name                 │  │
│ email (unique)       │  │
│ password             │  │
│ email_verified_at    │  │
│ remember_token       │  │
│ created_at           │  │
│ updated_at           │  │
└──────────────────────┘  │
                          │
                          │  1:N
                          │
         ┌────────────────┼────────────────┐
         │                │                │
         ▼                ▼                ▼
┌─────────────────┐  ┌──────────────┐  ┌────────────────────┐
│user_preferences │  │ reading_     │  │   saved_articles   │
├─────────────────┤  │   history    │  ├────────────────────┤
│ id (PK)         │  ├──────────────┤  │ id (PK)            │
│ user_id (FK)    │  │ id (PK)      │  │ user_id (FK)       │
│ category_id (FK)│  │ user_id (FK) │  │ article_id (FK)    │
│ created_at      │  │ article_id   │  │ created_at         │
│ updated_at      │  │   (FK)       │  └────────────────────┘
└─────────────────┘  │ read_at      │           │
         │           │ time_spent   │           │
         │           │   (seconds)  │           │
         │           │ created_at   │           │
         │           └──────────────┘           │
         │                  │                   │
         │                  │                   │
         │                  │                   │
         │                  ▼                   │
         │           ┌──────────────────────┐  │
         │           │      articles        │◄─┘
         │           ├──────────────────────┤
         └──────────►│ id (PK)              │
                     │ category_id (FK)     │
                     │ source_id (FK)       │
                     │ title                │
                     │ description          │
                     │ content (text)       │
                     │ summary (text)       │
                     │ url (unique)         │
                     │ image_url            │
                     │ author               │
                     │ published_at         │
                     │ status (enum)        │
                     │ processed_at         │
                     │ created_at           │
                     │ updated_at           │
                     └──────────────────────┘
                              │
                              │
                ┌─────────────┼─────────────┐
                │             │             │
                ▼             ▼             ▼
      ┌──────────────┐  ┌──────────┐  ┌─────────────┐
      │  categories  │  │ sources  │  │article_tags │
      ├──────────────┤  ├──────────┤  ├─────────────┤
      │ id (PK)      │  │ id (PK)  │  │ id (PK)     │
      │ name         │  │ name     │  │ article_id  │
      │ slug         │  │ url      │  │   (FK)      │
      │ description  │  │ logo_url │  │ tag         │
      │ icon         │  │ is_active│  │ created_at  │
      │ is_active    │  │ created_ │  └─────────────┘
      │ created_at   │  │   at     │
      │ updated_at   │  │ updated_ │
      └──────────────┘  │   at     │
                        └──────────┘
```

## Table Definitions

### 1. users
**Purpose**: Store user account information

| Column            | Type         | Attributes                    | Description                |
|-------------------|--------------|-------------------------------|----------------------------|
| id                | BIGINT       | PRIMARY KEY, AUTO_INCREMENT   | User ID                    |
| name              | VARCHAR(255) | NOT NULL                      | User's full name           |
| email             | VARCHAR(255) | UNIQUE, NOT NULL              | User's email               |
| password          | VARCHAR(255) | NOT NULL                      | Hashed password            |
| email_verified_at | TIMESTAMP    | NULL                          | Email verification time    |
| remember_token    | VARCHAR(100) | NULL                          | Remember me token          |
| created_at        | TIMESTAMP    | NULL                          | Account creation time      |
| updated_at        | TIMESTAMP    | NULL                          | Last update time           |

**Indexes**:
- PRIMARY KEY (id)
- UNIQUE (email)

---

### 2. categories
**Purpose**: Store news categories/topics

| Column      | Type         | Attributes                    | Description           |
|-------------|--------------|-------------------------------|-----------------------|
| id          | BIGINT       | PRIMARY KEY, AUTO_INCREMENT   | Category ID           |
| name        | VARCHAR(100) | NOT NULL                      | Category name         |
| slug        | VARCHAR(100) | UNIQUE, NOT NULL              | URL-friendly name     |
| description | TEXT         | NULL                          | Category description  |
| icon        | VARCHAR(50)  | NULL                          | Icon class/name       |
| is_active   | BOOLEAN      | DEFAULT 1                     | Active status         |
| created_at  | TIMESTAMP    | NULL                          | Creation time         |
| updated_at  | TIMESTAMP    | NULL                          | Last update time      |

**Indexes**:
- PRIMARY KEY (id)
- UNIQUE (slug)

**Sample Data**:
- Technology
- Business
- Sports
- Entertainment
- Health
- Science

---

### 3. sources
**Purpose**: Store news source information

| Column     | Type         | Attributes                    | Description          |
|------------|--------------|-------------------------------|----------------------|
| id         | BIGINT       | PRIMARY KEY, AUTO_INCREMENT   | Source ID            |
| name       | VARCHAR(255) | NOT NULL                      | Source name          |
| url        | VARCHAR(500) | NULL                          | Source website URL   |
| logo_url   | VARCHAR(500) | NULL                          | Logo image URL       |
| is_active  | BOOLEAN      | DEFAULT 1                     | Active status        |
| created_at | TIMESTAMP    | NULL                          | Creation time        |
| updated_at | TIMESTAMP    | NULL                          | Last update time     |

**Indexes**:
- PRIMARY KEY (id)

---

### 4. articles
**Purpose**: Store news articles with AI-generated summaries

| Column       | Type         | Attributes                    | Description              |
|--------------|--------------|-------------------------------|--------------------------|
| id           | BIGINT       | PRIMARY KEY, AUTO_INCREMENT   | Article ID               |
| category_id  | BIGINT       | FOREIGN KEY, NOT NULL         | Category reference       |
| source_id    | BIGINT       | FOREIGN KEY, NULL             | Source reference         |
| title        | VARCHAR(500) | NOT NULL                      | Article title            |
| description  | TEXT         | NULL                          | Short description        |
| content      | LONGTEXT     | NULL                          | Full article content     |
| summary      | TEXT         | NULL                          | AI-generated summary     |
| url          | VARCHAR(1000)| UNIQUE, NOT NULL              | Article URL              |
| image_url    | VARCHAR(1000)| NULL                          | Featured image URL       |
| author       | VARCHAR(255) | NULL                          | Article author           |
| published_at | TIMESTAMP    | NOT NULL                      | Publication date         |
| status       | ENUM         | DEFAULT 'pending'             | pending/processed/failed |
| processed_at | TIMESTAMP    | NULL                          | AI processing time       |
| created_at   | TIMESTAMP    | NULL                          | Record creation time     |
| updated_at   | TIMESTAMP    | NULL                          | Last update time         |

**Indexes**:
- PRIMARY KEY (id)
- FOREIGN KEY (category_id) REFERENCES categories(id)
- FOREIGN KEY (source_id) REFERENCES sources(id)
- UNIQUE (url)
- INDEX (category_id, published_at)
- INDEX (status)
- INDEX (published_at)

**Status Values**:
- `pending`: Article fetched, awaiting AI processing
- `processed`: Summary generated successfully
- `failed`: AI processing failed

---

### 5. user_preferences
**Purpose**: Store user's topic/category preferences

| Column      | Type      | Attributes                    | Description           |
|-------------|-----------|-------------------------------|-----------------------|
| id          | BIGINT    | PRIMARY KEY, AUTO_INCREMENT   | Preference ID         |
| user_id     | BIGINT    | FOREIGN KEY, NOT NULL         | User reference        |
| category_id | BIGINT    | FOREIGN KEY, NOT NULL         | Category reference    |
| created_at  | TIMESTAMP | NULL                          | Creation time         |
| updated_at  | TIMESTAMP | NULL                          | Last update time      |

**Indexes**:
- PRIMARY KEY (id)
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
- FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
- UNIQUE (user_id, category_id)

---

### 6. reading_history
**Purpose**: Track which articles users have read

| Column     | Type      | Attributes                    | Description              |
|------------|-----------|-------------------------------|--------------------------|
| id         | BIGINT    | PRIMARY KEY, AUTO_INCREMENT   | History ID               |
| user_id    | BIGINT    | FOREIGN KEY, NOT NULL         | User reference           |
| article_id | BIGINT    | FOREIGN KEY, NOT NULL         | Article reference        |
| read_at    | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP     | Time article was read    |
| time_spent | INT       | NULL                          | Seconds spent reading    |
| created_at | TIMESTAMP | NULL                          | Record creation time     |

**Indexes**:
- PRIMARY KEY (id)
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
- FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE
- UNIQUE (user_id, article_id)
- INDEX (user_id, read_at)

---

### 7. saved_articles
**Purpose**: Allow users to bookmark articles

| Column     | Type      | Attributes                    | Description           |
|------------|-----------|-------------------------------|-----------------------|
| id         | BIGINT    | PRIMARY KEY, AUTO_INCREMENT   | Saved ID              |
| user_id    | BIGINT    | FOREIGN KEY, NOT NULL         | User reference        |
| article_id | BIGINT    | FOREIGN KEY, NOT NULL         | Article reference     |
| created_at | TIMESTAMP | NULL                          | Bookmark time         |

**Indexes**:
- PRIMARY KEY (id)
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
- FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE
- UNIQUE (user_id, article_id)

---

### 8. article_tags (Optional)
**Purpose**: Tag articles for better categorization

| Column     | Type         | Attributes                    | Description       |
|------------|--------------|-------------------------------|-------------------|
| id         | BIGINT       | PRIMARY KEY, AUTO_INCREMENT   | Tag ID            |
| article_id | BIGINT       | FOREIGN KEY, NOT NULL         | Article reference |
| tag        | VARCHAR(100) | NOT NULL                      | Tag name          |
| created_at | TIMESTAMP    | NULL                          | Creation time     |

**Indexes**:
- PRIMARY KEY (id)
- FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE
- INDEX (tag)

---

## Relationships Summary

1. **User → Preferences**: One-to-Many (A user can have multiple category preferences)
2. **User → Reading History**: One-to-Many (A user can read multiple articles)
3. **User → Saved Articles**: One-to-Many (A user can save multiple articles)
4. **Category → Articles**: One-to-Many (A category contains multiple articles)
5. **Category → Preferences**: One-to-Many (A category can be preferred by multiple users)
6. **Source → Articles**: One-to-Many (A source publishes multiple articles)
7. **Article → Reading History**: One-to-Many (An article can be read by multiple users)
8. **Article → Tags**: One-to-Many (An article can have multiple tags)

---

## Design Rationale

### Why this structure?

1. **Normalization**: Data is properly normalized to avoid redundancy
2. **Scalability**: Can handle millions of articles with proper indexing
3. **Flexibility**: Easy to add new categories, sources, or features
4. **Performance**: Strategic indexes for fast queries
5. **Data Integrity**: Foreign keys ensure referential integrity
6. **Personalization**: User preferences and history enable personalized feeds

### Key Design Decisions

1. **Separate Categories Table**: Allows easy management and extension of topics
2. **Status Field**: Tracks article processing state for reliability
3. **Unique URL**: Prevents duplicate articles from same source
4. **Composite Indexes**: Optimized for common query patterns
5. **Soft Deletes**: Can be added if you want to keep deleted records

---

## Sample Queries

### Get personalized feed for user
```sql
SELECT a.* 
FROM articles a
INNER JOIN user_preferences up ON a.category_id = up.category_id
LEFT JOIN reading_history rh ON a.id = rh.article_id AND rh.user_id = ?
WHERE up.user_id = ?
  AND rh.id IS NULL
  AND a.status = 'processed'
ORDER BY a.published_at DESC
LIMIT 20;
```

### Get trending articles (most read)
```sql
SELECT a.*, COUNT(rh.id) as read_count
FROM articles a
INNER JOIN reading_history rh ON a.id = rh.article_id
WHERE a.published_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY a.id
ORDER BY read_count DESC
LIMIT 10;
```

### Get user's reading statistics
```sql
SELECT 
    c.name as category,
    COUNT(rh.id) as articles_read,
    SUM(rh.time_spent) as total_time
FROM reading_history rh
INNER JOIN articles a ON rh.article_id = a.id
INNER JOIN categories c ON a.category_id = c.id
WHERE rh.user_id = ?
GROUP BY c.id
ORDER BY articles_read DESC;
```
