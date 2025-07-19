<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class BaseApiService implements APIServiceInterface
{
    protected $apiKey;
    protected $baseUrl;
    protected $params;

    public function __construct(array $params = [])
    {
        $this->apiKey = config($this->getApiKeyConfigKey());
        $this->baseUrl = $this->getBaseUrl();
        $this->params = $params;
    }

    public function getEverything(array $params = []): array
    {
        $date = Carbon::now();
        $dateParams = $this->getDateParams();

        $params[$dateParams['from']] = $date->format($this->getDateFormat());
        if (isset($dateParams['to'])) {
            $params[$dateParams['to']] = $date->format($this->getDateFormat());
        }

        $data = $this->makeRequest($this->getSearchEndpoint(), $params);
        while (empty($data) || $data['status'] != false) {
            Log::info('Fetching articles from ' . static::class . ' for ' . $params[$dateParams['from']]);
            $date->subDay();
            $params[$dateParams['from']] = $date->format($this->getDateFormat());
            if (isset($dateParams['to'])) {
                $params[$dateParams['to']] = $date->format($this->getDateFormat());
            }
            $data = $this->makeRequest($this->getSearchEndpoint(), $params);
        }

        return $data;
    }

    public function makeRequest(string $endpoint, array $params = []): array
    {
        $allParams = array_merge([
            $this->getApiKeyParamName() => $this->apiKey,
        ], $this->getAdditionalRequestParams(), $this->params, $params);

        $response = Http::baseUrl($this->baseUrl)->get($endpoint, $allParams);
        if ($this->isRequestFailed($response)) {
            Log::error(static::class . ' request failed', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
            return ['status' => false];
        }

        return $this->toLocalDBSchema($response->json());
    }

    abstract protected function getApiKeyConfigKey(): string;

    abstract protected function getBaseUrl(): string;

    abstract protected function getSearchEndpoint(): string;

    abstract public function toLocalDBSchema(array $data): array;

    protected function getApiKeyParamName(): string
    {
        return 'api-key';
    }

    protected function getDateParams(): array
    {
        return [
            'from' => 'from-date',
            'to' => 'to-date',
        ];
    }

    protected function getDateFormat(): string
    {
        return 'Y-m-d';
    }

    protected function getAdditionalRequestParams(): array
    {
        return [];
    }

    protected function isRequestFailed(Response $response): bool
    {
        return $response->failed();
    }
}
