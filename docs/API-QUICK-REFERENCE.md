# API Keys - Quick Reference Card

## 🎯 Quick Decision

**For Demo/Interview:** Use Mock Services (no keys needed)  
**For Production:** Get real API keys (10 minutes setup)

---

## 🚀 Mock Services (Recommended for Demo)

**Configuration:**
```dotenv
USE_MOCK_SERVICES=true
NEWSAPI_KEY=
OPENAI_API_KEY=
```

**Advantages:**
- ✅ Zero setup time
- ✅ No costs
- ✅ Works offline
- ✅ Unlimited usage
- ✅ Perfect for demos

---

## 📰 NewsAPI Key

### Get Your Key (2 minutes)

1. **Register:** https://newsapi.org/register
2. **Verify email**
3. **Copy key from:** https://newsapi.org/account
4. **Add to .env:**
   ```dotenv
   NEWSAPI_KEY=your-key-here
   ```

### Free Tier
- 100 requests/day
- 80,000+ sources
- All categories

### Key Format
```
a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6
```

---

## 🤖 OpenAI Key

### Get Your Key (5 minutes)

1. **Sign up:** https://platform.openai.com/signup
2. **Verify phone**
3. **Create key:** https://platform.openai.com/api-keys
4. **Add to .env:**
   ```dotenv
   OPENAI_API_KEY=sk-proj-...
   ```

### Free Credit
- $5 free for new accounts
- ~2,500 summaries

### Key Format
```
sk-proj-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

---

## ⚙️ Switching Services

### Enable Real APIs
```bash
# Update .env
USE_MOCK_SERVICES=false
NEWSAPI_KEY=your-newsapi-key
OPENAI_API_KEY=your-openai-key

# Clear cache
php artisan config:clear

# Restart queue
php artisan queue:restart

# Fetch news
php artisan news:fetch
```

### Back to Mock
```bash
# Update .env
USE_MOCK_SERVICES=true

# Clear cache
php artisan config:clear
```

---

## 🧪 Test Your Keys

### Test NewsAPI
```bash
php artisan tinker
```
```php
$service = new App\Services\NewsAPIService();
$articles = $service->fetchNews('Technology', 5);
dd($articles);
```

### Test OpenAI
```bash
php artisan tinker
```
```php
$service = new App\Services\OpenAIService();
$summary = $service->generateSummary('Test article content here.');
echo $summary;
```

---

## 💰 Cost Calculator

### Mock Services
```
Cost: $0
Usage: Unlimited
```

### Real APIs (Small)
```
NewsAPI: $0 (free tier)
OpenAI: $2-5/month (1,000-2,500 summaries)
Total: $2-5/month
```

### Real APIs (Medium)
```
NewsAPI: $0 or $449/month
OpenAI: $20-50/month (10,000-25,000 summaries)
Total: $20-500/month
```

---

## 🐛 Common Issues

### "Invalid API key"
```bash
# Check .env file (no spaces)
# Verify key is correct
# Run: php artisan config:clear
```

### "Too many requests"
```bash
# Free tier limit reached
# Switch to mock: USE_MOCK_SERVICES=true
# Or wait 24 hours
```

### "Insufficient credits"
```bash
# OpenAI credit exhausted
# Add payment method
# Or use mock service
```

---

## 📊 Feature Matrix

| Feature | Mock | Real APIs |
|---------|------|-----------|
| Setup Time | 0 min | 10 min |
| Cost | $0 | $2-50/mo |
| Quality | Good | Excellent |
| Offline | ✅ Yes | ❌ No |
| Rate Limit | None | 100/day |

---

## 🎯 Interview Strategy

**What to say:**

> "I've implemented both mock and real API services. For today's demo, I'm using mock services which simulate realistic data without external dependencies. 
>
> The system uses interface-based design, so switching to production APIs is just a matter of configuration:
> - Get API keys (10 minutes)
> - Update .env file
> - Toggle USE_MOCK_SERVICES flag
>
> This approach allowed me to develop faster while maintaining production-ready architecture."

---

## 📞 Quick Links

- **NewsAPI Docs:** https://newsapi.org/docs
- **OpenAI Docs:** https://platform.openai.com/docs
- **Full Setup Guide:** API-SETUP-GUIDE.md
- **Project README:** README.md

---

## ✅ Pre-Demo Checklist

- [ ] .env configured
- [ ] USE_MOCK_SERVICES=true
- [ ] Database migrated
- [ ] Queue worker running
- [ ] News fetched: `php artisan news:fetch`
- [ ] Test account working: test@example.com / password

---

**Save this card for quick reference during development!** 📌
