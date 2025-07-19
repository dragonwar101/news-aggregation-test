<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Services\ArticleSyncService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncArticlesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'articles:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Syncs articles from the New York Times to the database';

    /**
     * The ArticleSyncService instance.
     *
     * @var \App\Services\ArticleSyncService
     */
    protected $articleSyncService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // $latestArticle = Article::latest()->first()?->published_at;

        // $this->info('Article sync started at ' . $latestArticle);
        $this->info('Starting article sync...');
        $sources = config('services.sources');
        $sourceNames = array_keys($sources);
        $selectedSources = $this->choice(
            'Which sources would you like to sync from? "," for multiselect',
            $sourceNames,
            '0,1,2',
            null,
            true
        );
        $selectedSources = array_map(function ($sourceName) use ($sources) {
            return new $sources[$sourceName]['class']($sources[$sourceName]['params']);
        }, $selectedSources);

        $this->info("Syncing articles from sources...");
        (new ArticleSyncService($selectedSources))->sync();

        $this->info('Article sync completed successfully.');
    }
}
