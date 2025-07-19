<?php

namespace App\Services;

use Illuminate\Http\Client\Response;

class GuardianApiService extends BaseApiService
{

    protected function getApiKeyConfigKey(): string
    {
        return 'services.guardian.key';
    }

    protected function getBaseUrl(): string
    {
        return 'https://content.guardianapis.com';
    }

    protected function getSearchEndpoint(): string
    {
        return '/search';
    }

    protected function getAdditionalRequestParams(): array
    {
        return ['show-fields' => 'all'];
    }

    protected function isRequestFailed(Response $response): bool
    {
        return $response->failed() || $response->json()['response']['status'] === 'error';
    }

    public function toLocalDBSchema(array $data): array
    {
        $transformedData = [];
        foreach ($data['response']['results'] as $article) {
            $transformedData[] = [
                'title' => $article['webTitle'],
                'slug' => $article['id'],
                'url' => $article['webUrl'],
                'description' => $article['fields']['trailText'] ?? '',
                'author' => $article['fields']['byline'] ?? '',
                'image_url' => $article['fields']['thumbnail'] ?? '',
                'published_at' => $article['webPublicationDate'],
                'content' => $article['fields']['body'] ?? '',
                'source' => $article['sectionId'],
                'original_source' => 'The Guardian',
            ];
        }

        return $transformedData;
    }
}

