<?php

namespace App\Services;

use Carbon\Carbon;

class MockNewsService
{
    public function fetchNews(array $categories, int $limit = 10): array
    {
        $mockArticles = $this->generateMockArticles($categories, $limit);
        
        return [
            'status' => 'ok',
            'totalResults' => count($mockArticles),
            'articles' => $mockArticles,
        ];
    }

    private function generateMockArticles(array $categories, int $limit): array
    {
        $articles = [];
        $templates = $this->getArticleTemplates();
        
        foreach ($categories as $category) {
            $categoryTemplates = $templates[$category['slug']] ?? $templates['technology'];
            $count = ceil($limit / count($categories));
            
            for ($i = 0; $i < $count; $i++) {
                $template = $categoryTemplates[array_rand($categoryTemplates)];
                
                $articles[] = [
                    'source' => ['id' => null, 'name' => $template['source']],
                    'author' => $template['author'],
                    'title' => $template['title'],
                    'description' => $template['description'],
                    'url' => 'https://example.com/article-' . uniqid(),
                    'urlToImage' => $this->getRandomImage($category['slug']),
                    'publishedAt' => Carbon::now()->subHours(rand(1, 48))->toIso8601String(),
                    'content' => $template['content'],
                ];
            }
        }
        
        return array_slice($articles, 0, $limit);
    }

    private function getArticleTemplates(): array
    {
        return [
            'technology' => [
                [
                    'source' => 'TechCrunch',
                    'author' => 'Sarah Johnson',
                    'title' => 'AI Startup Raises $50M to Revolutionize Code Generation',
                    'description' => 'New AI-powered development tool promises to increase developer productivity by 40%',
                    'content' => 'A promising AI startup has secured $50 million in Series B funding to expand its code generation platform. The tool uses advanced language models to help developers write code faster and with fewer bugs. Early adopters report significant productivity gains...',
                ],
                [
                    'source' => 'Wired',
                    'author' => 'Mike Chen',
                    'title' => 'Quantum Computing Breakthrough Achieves New Record',
                    'description' => 'Scientists demonstrate quantum supremacy with 1000-qubit processor',
                    'content' => 'Researchers at a leading quantum computing lab have achieved a major breakthrough by demonstrating a 1000-qubit quantum processor that maintains coherence for unprecedented durations. This advancement brings practical quantum computing closer to reality...',
                ],
                [
                    'source' => 'The Verge',
                    'author' => 'Lisa Park',
                    'title' => 'New Smartphone Features Revolutionary Camera System',
                    'description' => 'Latest flagship phone introduces AI-powered photography features',
                    'content' => 'The latest smartphone from a major manufacturer features an innovative camera system that uses AI to automatically adjust settings for optimal photos. The device has received praise from early reviewers for its low-light performance and portrait mode...',
                ],
            ],
            'business' => [
                [
                    'source' => 'Wall Street Journal',
                    'author' => 'David Martinez',
                    'title' => 'Tech Giants Report Strong Q4 Earnings',
                    'description' => 'Major technology companies exceed analyst expectations in quarterly results',
                    'content' => 'Several technology giants have reported better-than-expected earnings for the fourth quarter, driven by strong cloud computing and advertising revenues. Analysts are optimistic about continued growth in the sector...',
                ],
                [
                    'source' => 'Bloomberg',
                    'author' => 'Jennifer Lee',
                    'title' => 'Startup Ecosystem Shows Signs of Recovery',
                    'description' => 'Venture capital funding increases for first time in six quarters',
                    'content' => 'After a challenging period, the startup ecosystem is showing signs of recovery with venture capital funding increasing by 15% this quarter. Investors are particularly interested in AI, climate tech, and healthcare startups...',
                ],
            ],
            'sports' => [
                [
                    'source' => 'ESPN',
                    'author' => 'Tom Brady',
                    'title' => 'Championship Game Sets New Viewership Record',
                    'description' => 'Thrilling overtime finish captivates millions of fans worldwide',
                    'content' => 'Last night\'s championship game has set a new viewership record with over 150 million people tuning in globally. The game went into overtime and featured several dramatic plays that will be remembered for years to come...',
                ],
                [
                    'source' => 'Sports Illustrated',
                    'author' => 'Maria Rodriguez',
                    'title' => 'Young Athlete Signs Historic Contract',
                    'description' => 'Rising star becomes highest-paid player in league history',
                    'content' => 'A young phenom has signed a groundbreaking contract worth $500 million over 10 years, making them the highest-paid player in the league\'s history. The deal includes performance incentives and endorsement opportunities...',
                ],
            ],
            'health' => [
                [
                    'source' => 'HealthLine',
                    'author' => 'Dr. Emily Watson',
                    'title' => 'New Study Links Exercise to Improved Mental Health',
                    'description' => 'Research shows 30 minutes of daily activity reduces anxiety and depression',
                    'content' => 'A comprehensive study involving 10,000 participants has found that just 30 minutes of moderate exercise daily can significantly reduce symptoms of anxiety and depression. Researchers recommend a combination of aerobic and strength training...',
                ],
                [
                    'source' => 'Medical News Today',
                    'author' => 'Dr. James Wilson',
                    'title' => 'Breakthrough Treatment Shows Promise for Chronic Disease',
                    'description' => 'Clinical trials demonstrate 70% effectiveness rate',
                    'content' => 'A new treatment for a chronic condition has shown remarkable results in Phase 3 clinical trials, with a 70% effectiveness rate and minimal side effects. The treatment is expected to receive FDA approval within the next year...',
                ],
            ],
            'science' => [
                [
                    'source' => 'Nature',
                    'author' => 'Dr. Robert Chang',
                    'title' => 'Scientists Discover New Earth-Like Exoplanet',
                    'description' => 'Planet located in habitable zone of distant star system',
                    'content' => 'Astronomers have discovered a new exoplanet that shares many characteristics with Earth, including a similar size and orbit within its star\'s habitable zone. Further observations will determine if it has an atmosphere suitable for life...',
                ],
                [
                    'source' => 'Science Daily',
                    'author' => 'Dr. Amanda Foster',
                    'title' => 'New Species of Ancient Dinosaur Identified',
                    'description' => 'Fossil discovery rewrites understanding of dinosaur evolution',
                    'content' => 'Paleontologists have identified a new species of dinosaur from fossils discovered in a remote location. The finding provides new insights into how dinosaurs evolved and adapted to changing environments millions of years ago...',
                ],
            ],
            'entertainment' => [
                [
                    'source' => 'Variety',
                    'author' => 'Jessica Brown',
                    'title' => 'Blockbuster Film Breaks Box Office Records',
                    'description' => 'Latest superhero movie earns $300M in opening weekend',
                    'content' => 'The highly anticipated superhero film has shattered box office records with a $300 million opening weekend globally. Critics praise the film\'s stunning visual effects and compelling storyline, and sequel plans are already underway...',
                ],
            ],
        ];
    }

    private function getRandomImage(string $category): string
    {
        $images = [
            'technology' => 'https://images.unsplash.com/photo-1518770660439-4636190af475',
            'business' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f',
            'sports' => 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211',
            'health' => 'https://images.unsplash.com/photo-1505751172876-fa1923c5c528',
            'science' => 'https://images.unsplash.com/photo-1532094349884-543bc11b234d',
            'entertainment' => 'https://images.unsplash.com/photo-1574267432644-f88b7e1ad8cb',
        ];
        
        return $images[$category] ?? $images['technology'];
    }
}