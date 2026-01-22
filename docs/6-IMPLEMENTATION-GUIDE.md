# Implementation Guide

## Overview
This document provides a technical walkthrough of the codebase implementation.

## Project Structure

### Controllers (app/Http/Controllers/)
- **FeedController.php** - Handles personalized feed generation
- **ArticleController.php** - Manages article viewing and interactions
- **PreferenceController.php** - User preference management

### Models (app/Models/)
- **User.php** - User authentication and relationships
- **Article.php** - News articles with AI summaries
- **Category.php** - News categories/topics
- **UserPreference.php** - User-category relationships
- **ReadingHistory.php** - Article read tracking
- **SavedArticle.php** - Bookmarked articles

### Services (app/Services/)
- **MockNewsService.php** - Mock news data for development
- **NewsAPIService.php** - Real NewsAPI integration
- **MockAIService.php** - Mock AI summaries
- **OpenAIService.php** - Real OpenAI integration

### Jobs (app/Jobs/)
- **FetchNewsJob.php** - Background news fetching
- **GenerateSummaryJob.php** - AI summary generation

## Key Code Patterns

### Repository Pattern
Data access is abstracted through eloquent models with clear relationships.

### Service Layer
Business logic is separated from controllers into service classes.

### Queue Processing
Heavy operations (AI calls, news fetching) are processed asynchronously.

## Running the Code

See README.md for complete setup instructions.