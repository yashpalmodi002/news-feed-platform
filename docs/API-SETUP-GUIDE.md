# API Setup Guide

## Overview

This guide explains how to configure the Personalized News Feed Platform with both **mock services** (for development/demo) and **real API services** (for production).

---

## 🚀 Quick Start (No API Keys Needed)

### Option 1: Mock Services (Recommended for Demo)

The easiest way to run the application is with **mock services**. No API keys required!

**Steps:**

1. Open your `.env` file
2. Ensure these settings:
   ```dotenv
   USE_MOCK_SERVICES=true
   NEWSAPI_KEY=
   OPENAI_API_KEY=
   ```
3. Run the application:
   ```bash
   php artisan serve
   php artisan queue:work
   php artisan news:fetch
   ```

**What you get:**
- ✅ Realistic mock news articles
- ✅ AI-like summaries using intelligent templates
- ✅ All features work identically to production
- ✅ Zero cost
- ✅ No external dependencies
- ✅ Perfect for demos and development

---

## 🔑 Option 2: Real API Keys (Production)

For production deployment with real news and AI summaries, you'll need API keys.

---

## 📰 NewsAPI Setup

### Free Tier (Recommended for Testing)

**Limits:**
- 100 requests per day
- Access to 80,000+ news sources
- All categories available
- No credit card required

### Step-by-Step Registration

**Step 1: Visit NewsAPI**
- Go to: [https://newsapi.org/register](https://newsapi.org/register)

**Step 2: Create Account**
Fill in the registration form:
```
Name:           Your Full Name
Email:          your.email@example.com
Password:       Create a strong password
Country:        India (or your country)
```

**Step 3: Submit & Verify**
- Click "Submit"
- Check your email inbox
- Click the verification link

**Step 4: Get Your API Key**
- Login to: [https://newsapi.org/account](https://newsapi.org/account)
- Your API key will be displayed on the dashboard
- It looks like: `a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6`

**Step 5: Add to .env**
```dotenv
NEWSAPI_KEY=a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6
```

### API Key Example
```
Your API Key
────────────────────────────────────
a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6

⚠️ Keep this key private!
```

---

### Pricing Tiers

| Plan | Cost | Requests | Best For |
|------|------|----------|----------|
| **Developer** | Free | 100/day | Testing, demos, POCs |
| **Business** | $449/month | Unlimited | Production apps |
| **Enterprise** | Custom | Unlimited | Large-scale applications |

**For this POC:** The free tier is sufficient!

---

### Testing NewsAPI

After adding your key to `.env`, test it:

```bash
php artisan tinker
```

Then run:
```php
$newsService = new App\Services\NewsAPIService();
$articles = $newsService->fetchNews('Technology', 5);
print_r($articles);
```

**Expected Output:**
```
Array
(
    [0] => Array
        (
            [source] => Array
                (
                    [id] => techcrunch
                    [name] => TechCrunch
                )
            [title] => Some breaking tech news...
            [description] => Article description...
            [url] => https://techcrunch.com/...
            ...
        )
    ...
)
```

---

## 🤖 OpenAI Setup

### Pricing

**Free Credit:**
- $5 free credit for new accounts
- Expires after 3 months

**Pay-as-you-go:**
- GPT-3.5 Turbo: ~$0.002 per article summary
- Example: 1,000 summaries = $2.00
- Example: 10,000 summaries = $20.00

### Step-by-Step Registration

**Step 1: Visit OpenAI**
- Go to: [https://platform.openai.com/signup](https://platform.openai.com/signup)

**Step 2: Sign Up**
Choose your signup method:
- Email + Password
- Continue with Google
- Continue with Microsoft

**Step 3: Verify Email**
- Check your email
- Click verification link

**Step 4: Phone Verification**
- Enter your phone number
- Enter the 6-digit code sent via SMS

**Step 5: Create API Key**
1. Go to: [https://platform.openai.com/api-keys](https://platform.openai.com/api-keys)
2. Click **"Create new secret key"**
3. Name it: `News Feed Platform`
4. Copy the key immediately (shown only once!)

**Step 6: Add to .env**
```dotenv
OPENAI_API_KEY=sk-proj-abc123def456ghi789jkl012mno345...
```

### API Key Format
```
sk-proj-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

⚠️ **Important:** Store this key securely! It won't be shown again.

---

### Adding Payment Method (Optional)

If you exceed the free $5 credit:

**Step 1:** Go to billing
- Visit: [https://platform.openai.com/account/billing/overview](https://platform.openai.com/account/billing/overview)

**Step 2:** Add payment method
- Click "Add payment method"
- Enter credit card details

**Step 3:** Set monthly budget (Recommended)
- Click "Set up paid account"
- Set limit: $10/month (recommended for POC)
- This prevents unexpected charges

**Step 4:** Enable auto-recharge (Optional)
- Set minimum balance: $5
- Recharge amount: $10

---

### Testing OpenAI

After adding your key to `.env`, test it:

```bash
php artisan tinker
```

Then run:
```php
$aiService = new App\Services\OpenAIService();
$summary = $aiService->generateSummary('
This is a test article about artificial intelligence and its impact on society. 
AI technology is revolutionizing many industries including healthcare, finance, 
and transportation. Experts predict that AI will continue to grow in importance 
over the next decade.
');
echo $summary;
```

**Expected Output:**
```
This article examines artificial intelligence and its transformative impact 
across various sectors including healthcare, finance, and transportation. 
Industry experts anticipate continued growth and increasing significance 
of AI technology over the coming decade.
```

---

## ⚙️ Complete .env Configuration

### Development Mode (Mock Services)

```dotenv
# Application
APP_NAME="News Feed Platform"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=news_feed
DB_USERNAME=root
DB_PASSWORD=

# Queue
QUEUE_CONNECTION=database

# Mock Services (No API keys needed)
USE_MOCK_SERVICES=true
NEWSAPI_KEY=
OPENAI_API_KEY=
```

---

### Production Mode (Real APIs)

```dotenv
# Application
APP_NAME="News Feed Platform"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=your-database-host
DB_PORT=3306
DB_DATABASE=news_feed
DB_USERNAME=your-db-user
DB_PASSWORD=your-secure-password

# Queue (Redis recommended)
QUEUE_CONNECTION=redis
REDIS_HOST=your-redis-host
REDIS_PASSWORD=null
REDIS_PORT=6379

# Real API Services
USE_MOCK_SERVICES=false
NEWSAPI_KEY=a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6
OPENAI_API_KEY=sk-proj-abc123def456ghi789jkl012mno345...
```

---

## 🔄 Switching Between Mock and Real Services

### Switch to Real APIs

1. Get API keys (see above)
2. Update `.env`:
   ```dotenv
   USE_MOCK_SERVICES=false
   NEWSAPI_KEY=your-newsapi-key
   OPENAI_API_KEY=your-openai-key
   ```
3. Clear config cache:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```
4. Restart queue worker:
   ```bash
   # Stop existing worker (Ctrl+C)
   php artisan queue:work
   ```
5. Fetch news:
   ```bash
   php artisan news:fetch
   ```

### Switch Back to Mock

1. Update `.env`:
   ```dotenv
   USE_MOCK_SERVICES=true
   ```
2. Clear config cache:
   ```bash
   php artisan config:clear
   ```
3. Restart services and fetch news

---

## 🧪 Testing Your Configuration

### Test Mock Services

```bash
# Fetch news with mock service
php artisan news:fetch

# Check articles were created
php artisan tinker
>>> Article::count()
>>> Article::latest()->first()->title
```

### Test Real Services

```bash
# With real APIs configured
php artisan news:fetch

# Verify real data
php artisan tinker
>>> Article::latest()->first()->source->name  # Should show real source
>>> Article::latest()->first()->summary        # Should show AI-generated summary
```

---

## 🐛 Troubleshooting

### NewsAPI Issues

**Error: "Invalid API key"**
```
Solution: 
1. Check your API key in .env (no spaces)
2. Verify at https://newsapi.org/account
3. Make sure you verified your email
```

**Error: "Too many requests"**
```
Solution:
1. Free tier limit: 100 requests/day
2. Wait 24 hours or upgrade plan
3. Switch to mock services temporarily
```

**Error: "No articles returned"**
```
Solution:
1. Check your internet connection
2. Try a different category
3. Check NewsAPI status: https://newsapi.org/status
```

---

### OpenAI Issues

**Error: "Invalid API key"**
```
Solution:
1. Check key format: starts with sk-proj-
2. Copy/paste carefully (no spaces)
3. Generate a new key if needed
```

**Error: "Insufficient credits"**
```
Solution:
1. Check balance: https://platform.openai.com/account/usage
2. Add payment method
3. Use mock service temporarily
```

**Error: "Rate limit exceeded"**
```
Solution:
1. Free tier: 3 RPM (requests per minute)
2. Add delay between requests
3. Upgrade to paid tier
```

**Error: "Model not found"**
```
Solution:
1. Ensure you're using: gpt-3.5-turbo
2. Check OpenAI service status
3. Review API documentation
```

---

## 💰 Cost Estimation

### Development/Demo (Mock Services)
- **Cost:** $0/month
- **Usage:** Unlimited
- **Best for:** Development, testing, demos

### Small Production (Free Tiers)
- **NewsAPI:** Free (100 requests/day)
- **OpenAI:** $0-5/month (free credit)
- **Total:** $0-5/month
- **Best for:** Small blogs, personal projects

### Medium Production (Paid Tiers)
- **NewsAPI:** Free (if < 100 requests/day)
- **OpenAI:** $20-50/month (10,000-25,000 summaries)
- **Server:** $20-50/month (VPS)
- **Total:** $40-100/month
- **Best for:** 1,000-10,000 users

### Large Production
- **NewsAPI:** $449/month (unlimited)
- **OpenAI:** $100-500/month (custom usage)
- **Infrastructure:** $200-1,000/month
- **Total:** $749-1,949/month
- **Best for:** 10,000+ users

---

## 📊 Feature Comparison

| Feature | Mock Services | Real APIs |
|---------|--------------|-----------|
| **Setup Time** | 0 minutes | 10 minutes |
| **Cost** | $0 | $0-50/month |
| **News Quality** | Realistic templates | Real news sources |
| **AI Summaries** | Template-based | GPT-3.5 powered |
| **Internet Required** | No | Yes |
| **Rate Limits** | None | 100/day (free tier) |
| **Best For** | Development, demos | Production |

---

## 🎯 Recommended Approach

### For POC/Demo (Current)
✅ **Use Mock Services**
- No setup required
- Zero costs
- Demonstrates full functionality
- Perfect for interviews

### For MVP Launch
✅ **Use Free Tiers**
- NewsAPI: Free (100/day)
- OpenAI: $5 free credit
- Total cost: $0-5 for first month

### For Production
✅ **Upgrade as Needed**
- Start with free tiers
- Monitor usage
- Upgrade when limits reached
- Scale based on actual needs

---

## 📝 Configuration Checklist

### Before Running Application

- [ ] `.env` file created from `.env.example`
- [ ] Database configured and migrated
- [ ] `USE_MOCK_SERVICES` set to `true` (for demo) or `false` (for production)
- [ ] API keys added (if using real services)
- [ ] Config cache cleared: `php artisan config:clear`
- [ ] Queue worker running: `php artisan queue:work`

### For Production Deployment

- [ ] API keys obtained and tested
- [ ] `USE_MOCK_SERVICES=false` in `.env`
- [ ] Redis configured for queue and cache
- [ ] Supervisor configured for queue workers
- [ ] Monitoring set up (Sentry, New Relic)
- [ ] Rate limiting configured
- [ ] Error handling tested
- [ ] Backup strategy in place

---

## 🔒 Security Best Practices

### Protecting API Keys

1. **Never commit .env to Git**
   ```bash
   # Already in .gitignore
   .env
   .env.backup
   ```

2. **Use environment variables in production**
   ```bash
   # On server
   export NEWSAPI_KEY="your-key"
   export OPENAI_API_KEY="your-key"
   ```

3. **Rotate keys regularly**
   - Generate new keys every 90 days
   - Revoke old keys immediately

4. **Monitor usage**
   - Check NewsAPI dashboard daily
   - Review OpenAI usage weekly
   - Set up billing alerts

5. **Use read-only keys when possible**
   - NewsAPI keys are read-only by default
   - OpenAI keys should have minimal permissions

---

## 📞 Support & Resources

### NewsAPI
- **Documentation:** https://newsapi.org/docs
- **Support:** support@newsapi.org
- **Status Page:** https://newsapi.org/status

### OpenAI
- **Documentation:** https://platform.openai.com/docs
- **Community Forum:** https://community.openai.com
- **Status Page:** https://status.openai.com
- **Support:** help.openai.com

### This Project
- **GitHub Issues:** [Create an issue](https://github.com/yashpalmodi002/news-feed-platform/issues)
- **Email:** [your-email@example.com]

---

## 🚀 Quick Reference

### Essential Commands

```bash
# Switch to mock services
echo "USE_MOCK_SERVICES=true" >> .env
php artisan config:clear

# Switch to real APIs
echo "USE_MOCK_SERVICES=false" >> .env
php artisan config:clear

# Test news fetching
php artisan news:fetch

# Check article count
php artisan tinker
>>> Article::count()

# View latest article
php artisan tinker
>>> Article::latest()->first()

# Clear everything and start fresh
php artisan config:clear
php artisan cache:clear
php artisan queue:restart
```

---

## ✅ Success Indicators

### Mock Services Working
- ✅ Articles created within seconds
- ✅ Summaries generated instantly
- ✅ No API errors in logs
- ✅ Queue jobs processed quickly

### Real APIs Working
- ✅ Real news sources visible (TechCrunch, BBC, etc.)
- ✅ Natural language AI summaries
- ✅ Up-to-date news articles
- ✅ Source logos and images present

---

## 📖 Additional Resources

### Laravel Documentation
- **Queue System:** https://laravel.com/docs/10.x/queues
- **Task Scheduling:** https://laravel.com/docs/10.x/scheduling
- **Environment Configuration:** https://laravel.com/docs/10.x/configuration

### API Documentation
- **NewsAPI Endpoints:** https://newsapi.org/docs/endpoints
- **OpenAI API Reference:** https://platform.openai.com/docs/api-reference
- **GPT-3.5 Turbo Guide:** https://platform.openai.com/docs/guides/text-generation

---

## 🎓 Learning Path

### If You're New to APIs

1. **Start with mock services** - Understand the flow
2. **Get NewsAPI key** - Test with real news
3. **Add OpenAI key** - Experience AI summaries
4. **Monitor costs** - Track usage patterns
5. **Optimize** - Implement caching, rate limiting

---

## Summary

This guide covers everything you need to configure and run the Personalized News Feed Platform with both mock and real API services. 

**For interview/demo:** Use mock services (no setup required)  
**For production:** Follow the API key setup instructions above

The architecture is designed to work seamlessly with both approaches, making it easy to switch based on your needs.

---

**Questions?** Open an issue on GitHub or contact the development team.

**Good luck with your deployment!** 🚀
