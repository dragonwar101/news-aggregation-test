<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Str;

class NewsApiService extends BaseApiService
{

    protected function getApiKeyConfigKey(): string
    {
        return 'services.newsapi.key';
    }

    protected function getBaseUrl(): string
    {
        return 'https://newsapi.org/v2';
    }

    protected function getSearchEndpoint(): string
    {
        return '/everything';
    }

    protected function getApiKeyParamName(): string
    {
        return 'apiKey';
    }

    protected function getDateParams(): array
    {
        return [
            'from' => 'from',
            'to' => 'to',
        ];
    }

    protected function getAdditionalRequestParams(): array
    {
        return ['sources' => 'al-jazeera-english'];
    }

    protected function isRequestFailed(Response $response): bool
    {
        return $response->failed() || $response->json()['status'] === 'error';
    }

    public function toLocalDBSchema(array $data): array
    {
        $transformedData = [];
        foreach ($data['articles'] ?? [] as $article) {
            $transformedData[] = [
                'title' => $article['title'],
                'slug' => Str::slug($article['title']),
                'url' => $article['url'],
                'description' => $article['description'],
                'author' => $article['author'],
                'image_url' => $article['urlToImage'],
                'published_at' => $article['publishedAt'],
                'content' => $article['content'],
                'source' => $article['source']['id'],
                'original_source' => $article['source']['name'] . ' - via news API',
            ];
        }

        return $transformedData;
    }
}

