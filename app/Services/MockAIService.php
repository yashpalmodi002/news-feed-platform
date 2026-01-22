<?php

namespace App\Services;

class MockAIService
{
    public function generateSummary(string $title, string $content): string
    {
        // Generate a mock summary based on title keywords
        $summaryTemplates = [
            'technology' => 'This article discusses recent technological advancements and their impact on the industry.',
            'business' => 'The article examines business trends and economic developments affecting markets.',
            'sports' => 'This piece covers recent sporting events and athlete performances.',
            'health' => 'The article explores health research findings and wellness recommendations.',
            'science' => 'This piece delves into scientific discoveries and research breakthroughs.',
            'entertainment' => 'The article reviews entertainment news and cultural developments.',
        ];
        
        // Simple keyword matching for mock summary selection
        $titleLower = strtolower($title);
        $summary = '';
        
        foreach ($summaryTemplates as $topic => $template) {
            if (str_contains($titleLower, $topic)) {
                $summary = $template;
                break;
            }
        }
        
        // Default summary if no match
        if (empty($summary)) {
            $summary = 'This article provides insights and analysis on current events and developments in the field.';
        }
        
        // Add a bit of context from the title
        $words = explode(' ', $title);
        $keyWords = array_slice($words, 0, min(5, count($words)));
        $summary .= ' Key topics include: ' . implode(', ', $keyWords) . '.';
        
        return $summary;
    }
}