<?php

namespace App\Services;

interface APIServiceInterface
{
    /**
     * Fetch everything from the API.
     *
     * @param array $params
     * @return array
     */
    public function getEverything(array $params = []): array;

    /**
     * Make a request to the API.
     *
     * @param string $endpoint
     * @param array $params
     * @return array
     */
    public function makeRequest(string $endpoint, array $params = []): array;

    /**
     * Transform data from API schema to local DB schema.
     *
     * @param array $data
     * @return array
     */
    public function toLocalDBSchema(array $data): array;
}
