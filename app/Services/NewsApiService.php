<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NewsApiService implements APIServiceInterface
{
    protected $apiKey;
    protected $baseUrl;
    protected $params;

    public function __construct(array $params = [])
    {
        $this->apiKey = config('services.newsapi.key');
        $this->baseUrl = 'https://newsapi.org/v2';
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
        $params['from'] = $date->format('Y-m-d');
        $params['to'] = $date->format('Y-m-d');
        // $sources = $this->getSources();
        // $sourcesChunks = array_chunk(array_column($sources, 'id'), 20);
        // for simplicity, we will fetch all articles from al-jazeera-english only
        $params['sources'] = 'al-jazeera-english';
        $result = $this->makeRequest('/everything', $params);
        while(empty($result)) {
            Log::info('Fetching articles from News API in '.$params['from']);
            $date->subDay();
            $params['from'] = $date->format('Y-m-d');
            $params['to'] = $date->format('Y-m-d');
            $result = $this->makeRequest('/everything', $params);
        }
        return $result;
    }

    public function getSources(): array
    {
        $response = Http::baseUrl($this->baseUrl)->get('/top-headlines/sources', [
            'apiKey' => $this->apiKey,
        ]);
        if ($response->failed() || $response->json()['status'] == 'error') {
            Log::error('News API request failed', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
            return [];
        }
        return $response->json('sources');
    }

    public function requestArticlePage(array $params): array
    {
        $allParams = array_merge([
            'apiKey' => $this->apiKey,
        ], $this->params, $params);
        $response = Http::baseUrl($this->baseUrl)->get('/everything', $allParams);
        
        if ($response->failed() || $response->json()['status'] == 'error') {
            dd($response->json());
            Log::error('News API request failed', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
            return ['articles' => []];
        }
        return $response->json();
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
        $params['pageSize'] = 100;
        $result = $this->requestArticlePage($params);
        // $totalResults = $result['totalResults'];
        // $pages = ceil($totalResults / $params['pageSize']);
        // for simplicity we'll fetch only one page as more than that is paid
        // $pages = 1;
        // $currentPage = 2;
        // for ($i = $currentPage; $i <= $pages; $i++) {
        //     $params['page'] = $i;
        //     $result['articles'] = array_merge(
        //         $result['articles'], 
        //         $this->requestArticlePage($params)['articles']
        //     );
        // }
        return $this->toLocalDBSchema($result);
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
        foreach ($data['articles'] as $article) {
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
