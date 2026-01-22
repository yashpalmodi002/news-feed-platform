<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
{
    // Bind News Service
    $this->app->bind(
        \App\Services\NewsServiceInterface::class,
        function () {
            return config('services.use_mock_services')
                ? new \App\Services\MockNewsService()
                : new \App\Services\NewsAPIService();
        }
    );

    // Bind AI Service  
    $this->app->bind(
        \App\Services\AIServiceInterface::class,
        function () {
            return config('services.use_mock_services')
                ? new \App\Services\MockAIService()
                : new \App\Services\OpenAIService();
        }
    );
}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
