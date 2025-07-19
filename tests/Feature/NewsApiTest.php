<?php

use App\Models\Article;
use Illuminate\Support\Facades\Artisan;

use function Pest\Laravel\getJson;

it('can get a list of articles', function () {
    // Arrange
    Artisan::call('articles:sync');

    // Act & Assert
    getJson(route('api.news.index'))
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'title',
                    'description',
                    'url',
                    'image_url',
                    'slug',
                    'source',
                    'original_source',
                ],
            ],
        ]);
});

it('can get a single article', function () {
    // Arrange
    $article = Article::all()->random();

    // Act & Assert
    getJson(route('api.news.show', ['slug' => $article->slug]))
        ->assertOk()
        ->assertJsonFragment([
            'data' => [
                'title' => $article->title,
                'slug' => $article->slug,
                'description' => $article->description,
                'source' => $article->source,
                'url' => $article->url,
                'image_url' => $article->image_url,
                'published_at' => $article->published_at,
                'author' => $article->author,
                'content' => $article->content,
                'original_source' => $article->original_source,
            ],
        ]);
});

it('returns not found for an invalid article', function () {
    // Act & Assert
    getJson(route('api.news.show', ['slug' => 'invalid-slug']))
        ->assertNotFound();
});
