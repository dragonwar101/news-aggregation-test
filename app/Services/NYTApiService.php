<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Str;

class NYTApiService extends BaseApiService
{

    protected function getApiKeyConfigKey(): string
    {
        return 'services.newyorktimes.key';
    }

    protected function getBaseUrl(): string
    {
        return 'https://api.nytimes.com/svc/search/v2';
    }

    protected function getSearchEndpoint(): string
    {
        return '/articlesearch.json';
    }

    protected function getDateParams(): array
    {
        return [
            'from' => 'begin_date',
            'to' => 'end_date',
        ];
    }

    protected function getDateFormat(): string
    {
        return 'Ymd';
    }

    protected function getAdditionalRequestParams(): array
    {
        return ['sort' => 'newest'];
    }

    protected function isRequestFailed(Response $response): bool
    {
        return $response->failed() || !$response->json()['response'];
    }

    public function toLocalDBSchema(array $data): array
    {
        $transformedData = [];
        foreach ($data['response']['docs'] ?? [] as $article) {
            $transformedData[] = [
                'title' => $article['headline']['main'],
                'slug' => Str::slug($article['headline']['main']),
                'url' => $article['web_url'],
                'author' => $article['byline']['original'],
                'description' => $article['abstract'],
                'image_url' => $article['multimedia']['default']['url'] ?? '',
                'published_at' => $article['pub_date'],
                'content' => $article['multimedia']['caption'] ?? '',
                'source' => $article['source'],
                'original_source' => 'New York Times',
            ];
        }

        return $transformedData;
    }
}