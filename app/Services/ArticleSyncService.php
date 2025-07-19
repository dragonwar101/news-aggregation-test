<?php

namespace App\Services;

use App\Models\Article;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ArticleSyncService
{
    protected $articleServices;

    public function __construct(array $articleServices)
    {
        $this->articleServices = $articleServices;
    }

    /**
     * Sync articles from all sources to the database.
     */
    public function sync(): void
    {
        Log::info('Starting articles sync from all sources.');
        $articles = [];
        foreach ($this->articleServices as $articleService) {
            $articles = array_merge($articles, $articleService->getEverything());
        }
        usort($articles, function ($a, $b) {
            return Carbon::parse($a['published_at'])->gte(Carbon::parse($b['published_at'])) ? -1 : 1;
        });

        $from = null;

        if (end($articles)) {
            $from = end($articles)['published_at'];
            Log::info("Fetching articles published after: {$from}");
        } else {
            Log::info('No existing articles found. Fetching all available articles.');
        }

        if (!$articles || empty($articles)) {
            Log::warning('No new articles found or failed to fetch from New York Times.');
            return;
        }
        $syncedCount = 0;

        foreach ($articles as $articleData) {
            if (empty($articleData['url'])) {
                continue;
            }
            Article::updateOrCreate(
                ['slug' => $articleData['slug']],
                [
                    'title' => $articleData['title'],
                    'url' => $articleData['url'],
                    'source' => $articleData['source'],
                    'original_source' => $articleData['original_source'],
                    'author' => $articleData['author'],
                    'image_url' => $articleData['image_url'],
                    'description' => $articleData['description'],
                    'content' => $articleData['content'],
                    'published_at' => Carbon::parse($articleData['published_at']),
                ]
            );
            $syncedCount++;
        }

        Log::info("Article sync completed. Synced {$syncedCount} articles.");
    }
}
