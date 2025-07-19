<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GuardianApiService implements APIServiceInterface
{
    protected $apiKey;
    protected $baseUrl;
    protected $params;

    public function __construct(array $params = [])
    {
        $this->apiKey = config('services.guardian.key');
        $this->baseUrl = 'https://content.guardianapis.com';
        $this->params = $params;
    }

    /**
     * Fetch everything from the News API.
     *
     * @param array $params
     * @return array
     */
    public function getEverything(array $params = []): array
    {
        $date = Carbon::now();
        $params['from-date'] = $date->format('Y-m-d');
        $params['to-date'] = $date->format('Y-m-d');
        $data = $this->makeRequest('/search', $params);
        while(empty($data)) {
            Log::info('Fetching articles from Guardian API in '.$params['from-date']);
            $date->subDay();
            $params['from-date'] = $date->format('Y-m-d');
            $params['to-date'] = $date->format('Y-m-d');
            $data = $this->makeRequest('/search', $params);
        }
        return $data;
    }

    /**
     * Make a request to the News API.
     *
     * @param string $endpoint
     * @param array $params
     * @return array
     */
    public function makeRequest(string $endpoint, array $params = []): array
    {
        $allParams = array_merge([
            'api-key' => $this->apiKey,
            'show-fields' => 'all',
        ], $this->params, $params);

        $response = Http::baseUrl($this->baseUrl)->get($endpoint, $allParams);
        if ($response->failed() || $response->json()['response']['status'] == 'error') {
            Log::error('News API request failed', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
            return [];
        }


        return $this->toLocalDBSchema($response->json());
    }

    /**
     * Transform data from News API schema to local DB schema.
     *
     * @param array $data
     * @return array
     */
    public function toLocalDBSchema(array $data): array
    {
        $transformedData = [];
        foreach ($data['response']['results'] as $article) {
            $transformedData[] = [
                'title' => $article['webTitle'],
                'slug' => $article['id'],
                'url' => $article['webUrl'],
                'description' => $article['fields']['trailText'],
                'author' => $article['fields']['byline'],
                'image_url' => $article['fields']['thumbnail'],
                'published_at' => $article['webPublicationDate'],
                'content' => $article['fields']['body'],
                'source' => $article['sectionId'],
                'original_source' => 'The Guardian',
            ];
        }

        return $transformedData;
    }
}
