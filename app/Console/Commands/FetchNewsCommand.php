<?php

namespace App\Console\Commands;

use App\Jobs\FetchNewsJob;
use Illuminate\Console\Command;

class FetchNewsCommand extends Command
{
    protected $signature = 'news:fetch {--limit=50 : Number of articles to fetch}';
    protected $description = 'Fetch news articles from external APIs';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        
        $this->info("Fetching up to {$limit} news articles...");
        
        FetchNewsJob::dispatch($limit);
        
        $this->info('News fetch job dispatched. Check queue worker for progress.');
        $this->info('Run "php artisan queue:work" to process the job.');
        
        return self::SUCCESS;
    }
}