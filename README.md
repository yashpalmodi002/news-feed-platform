# 📰 Personalized News Feed Platform

A Laravel-based news aggregation platform with AI-powered summarization and personalized content delivery.

**Live Demo**: [Add if deployed] | **Documentation**: [Link to docs]

---

## 🎯 Project Overview

This project was developed as a technical interview submission for **Newboxes**, demonstrating:
- System architecture design
- AI/ML integration
- Laravel expertise
- Database design
- Clean code practices
- Production-ready implementation

---

## ✨ Key Features

- 📰 **Multi-source News Aggregation** from NewsAPI
- 🤖 **AI-Powered Summaries** using OpenAI GPT-3.5
- 🎯 **Personalized Feed** based on user preferences
- 📊 **Reading History** tracking
- 💾 **Bookmark Articles** for later
- 🔍 **Category Filtering** (Technology, Business, Sports, etc.)
- 📱 **Responsive Design** with Tailwind CSS

---

## 🏗️ Architecture
```
┌─────────────────────────────────────┐
│     Presentation Layer              │  Blade + Tailwind CSS
├─────────────────────────────────────┤
│     Application Layer               │  Controllers + Routes
├─────────────────────────────────────┤
│     Business Logic Layer            │  Services + Jobs
├─────────────────────────────────────┤
│     Data Access Layer               │  Eloquent Models
├─────────────────────────────────────┤
│     Infrastructure Layer            │  Queue + External APIs
└─────────────────────────────────────┘
```

**Key Design Patterns:**
- Repository Pattern
- Service Layer Architecture
- Queue-based Processing
- Interface-based Design

---

## 💻 Tech Stack

### Backend
- **Framework**: Laravel 10.x
- **Language**: PHP 8.1+
- **Database**: MySQL 8.0
- **Queue**: Laravel Queue (database driver)

### Frontend
- **Template Engine**: Blade
- **CSS Framework**: Tailwind CSS
- **JavaScript**: Vanilla JS

### External Services
- **News API**: NewsAPI.org
- **AI API**: OpenAI GPT-3.5

---

## 🚀 Installation

### Prerequisites
- PHP 8.1 or higher
- Composer
- MySQL 8.0 or higher
- Node.js & NPM

### Setup Steps

1. **Clone the repository**
```bash
git clone https://github.com/yashpalmodi002/news-feed-platform.git
cd news-feed-platform
```

2. **Install PHP dependencies**
```bash
composer install
```

3. **Install Node dependencies**
```bash
npm install
```

4. **Environment Configuration**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Configure Database**

Edit `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=news_feed
DB_USERNAME=root
DB_PASSWORD=your_password
```

6. **Configure API Keys (Optional)**
```env
# For mock services (no API keys needed)
USE_MOCK_SERVICES=true

# For production (requires API keys)
NEWSAPI_KEY=your_newsapi_key
OPENAI_API_KEY=your_openai_key
```

7. **Create Database**
```bash
mysql -u root -p
CREATE DATABASE news_feed;
exit;
```

8. **Run Migrations**
```bash
php artisan migrate --seed
```

9. **Build Assets**
```bash
npm run build
```

10. **Start Application**
```bash
# Terminal 1: Laravel server
php artisan serve

# Terminal 2: Queue worker
php artisan queue:work

# Terminal 3: Fetch news
php artisan news:fetch
```

---

## 🎮 Usage

### Default Test Account
- **Email**: test@example.com
- **Password**: password

### Application Flow
1. Login or register
2. Select preferred topics
3. View personalized news feed
4. Read articles with AI summaries
5. Save articles for later
6. System tracks reading history

---

## 📊 Database Schema

**Core Tables:**
- `users` - User accounts
- `categories` - News topics
- `sources` - News publishers
- `articles` - News with AI summaries
- `user_preferences` - User topic selections
- `reading_history` - Reading tracking
- `saved_articles` - Bookmarks

[View detailed schema →](docs/database-schema.md)

---

## 🎨 Project Structure
```
news-feed-platform/
├── app/
│   ├── Console/Commands/    # Artisan commands
│   ├── Http/Controllers/    # Request handlers
│   ├── Models/              # Eloquent models
│   ├── Services/            # Business logic
│   ├── Jobs/                # Queue jobs
│   └── Repositories/        # Data access
├── database/
│   ├── migrations/          # Schema definitions
│   └── seeders/             # Sample data
├── resources/
│   └── views/               # Blade templates
└── routes/
    └── web.php              # Application routes
```

---

## 🔧 Configuration

### Mock Services (Development)
```env
USE_MOCK_SERVICES=true
```
- No API costs
- Instant responses
- Perfect for demo

### Real APIs (Production)
```env
USE_MOCK_SERVICES=false
NEWSAPI_KEY=your_actual_key
OPENAI_API_KEY=your_actual_key
```

---

## 🧪 Testing
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
```

---

## 📈 Scalability

### Current (POC)
- Single server
- 10-100 concurrent users

### Phase 1 (100-1000 users)
- Redis caching
- Multiple queue workers
- Database read replicas

### Phase 2 (1000-10000 users)
- Load balancer
- CDN for images
- Elasticsearch for search

### Phase 3 (10000+ users)
- Microservices
- Distributed caching
- Auto-scaling

---

## 🔐 Security Features

- ✅ Authentication (Laravel Breeze)
- ✅ CSRF Protection
- ✅ SQL Injection Prevention
- ✅ XSS Protection
- ✅ Password Hashing (bcrypt)
- ✅ API Rate Limiting

---

## 📝 Documentation

- [System Architecture](docs/architecture.md)
- [AI Pipeline](docs/ai-pipeline.md)
- [Database Schema](docs/database-schema.md)
- [API Documentation](docs/api.md)
- [Deployment Guide](docs/deployment.md)

---

## 🤝 Contributing

This is a technical interview project. Contributions are welcome after review.

1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

---

## 📄 License

This project is open-source software licensed under the MIT license.

---

## 👤 Author

**Yash Parmeshwar Modi**

- GitHub: [@yashpalmodi002](https://github.com/yashpalmodi002)
- LinkedIn: [Add your LinkedIn]
- Email: [Your Email]

---

## 🙏 Acknowledgments

- **Newboxes** - For the technical interview opportunity
- **NewsAPI.org** - News aggregation service
- **OpenAI** - AI summarization
- **Laravel Community** - Framework and support
- **Tailwind CSS** - UI styling

---

## 📞 Support

For questions or issues:
- Create an issue on GitHub
- Email: [your-email@example.com]

---

**Built with ❤️ for Newboxes Technical Interview**

*Time invested: 6-8 hours | Every line written with care*