<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NYTApiService implements APIServiceInterface
{
    protected $apiKey;
    protected $baseUrl;
    protected $params;

    public function __construct(array $params = [])
    {
        $this->apiKey = config('services.newyorktimes.key');
        $this->baseUrl = 'https://api.nytimes.com/svc/search/v2';
        $this->params = $params;
    }

    public function getEverything(array $params = []) : array
    {
        $date = Carbon::now();
        $params['from-date'] = $date->format('Y-m-d');
        $params['to-date'] = $date->format('Y-m-d');
        $data = $this->makeRequest('/articlesearch.json', $params);
        while(empty($data)) {
            Log::info('Fetching articles from NYT API in '.$params['from-date']);
            $date->subDay();
            $params['from-date'] = $date->format('Y-m-d');
            $params['to-date'] = $date->format('Y-m-d');
            $data = $this->makeRequest('/articlesearch.json', $params);
        }
        return $data;
    }

    public function makeRequest(string $endpoint, array $params = []) : array
    {
        $allParams = array_merge([
            'sort' => 'newest',
            'api-key' => $this->apiKey,
        ], $this->params, $params);
        $response = Http::baseUrl($this->baseUrl)->get($endpoint, $allParams);
        if ($response->failed()) {
            dd($response->json());
            Log::error('NYT API request failed', [
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
        foreach ($data['response']['docs'] ?? [] as $article) {
            $transformedData[] = [
                'title' => $article['headline']['main'],
                'slug' => Str::slug($article['headline']['main']),
                'url' => $article['web_url'],
                'author' => $article['byline']['original'],
                'description' => $article['abstract'],
                'image_url' => $article['multimedia']['default']['url'],
                'published_at' => $article['pub_date'],
                'content' => $article['multimedia']['caption'],
                'source' => $article['source'],
                'original_source' => 'New York Times',
            ];
        }

        return $transformedData;
    }
}