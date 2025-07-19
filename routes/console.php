<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\SyncArticlesCommand;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(SyncArticlesCommand::class, [])
    ->everyMinute()
    ->withoutOverlapping()
    ->onFailure(function () {
        Log::error('NewsAPI sync failed in scheduled job.');
    })
    ->onSuccess(function () {
        Log::info('NewsAPI sync completed successfully in scheduled job.');
    });
