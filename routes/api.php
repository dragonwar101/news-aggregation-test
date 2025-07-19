<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NewsController;
Route::as('api.')->group(function () {
    Route::prefix('news')->as('news.')->group(function () {
        Route::get('', [NewsController::class, 'index'])->name('index');
        Route::get('{slug}', [NewsController::class, 'show'])->name('show')->where('slug', '(.*)');
    });
    Route::get('summary', [NewsController::class, 'summary'])->name('summary');
});
