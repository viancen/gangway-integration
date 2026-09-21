<?php

namespace Gangway\Laravel\Resources\Concerns;

use Gangway\Laravel\Client;
use Gangway\Laravel\Responses\ApiObject;
use Gangway\Laravel\Responses\PaginatedResponse;

trait InteractsWithClient
{
    public function __construct(protected Client $client) {}

    /**
     * @param  array<string, mixed>  $query
     */
    protected function list(string $path, array $query = []): PaginatedResponse
    {
        return $this->client->getPaginated($path, $query);
    }

    /**
     * @param  array<string, mixed>  $query
     */
    protected function find(string $path, array $query = []): ApiObject
    {
        return $this->client->get($path, $query);
    }

    /**
     * @param  array<string, mixed>  $body
     */
    protected function create(string $path, array $body, ?string $idempotencyKey = null): ApiObject
    {
        return $this->client->post($path, $body, $idempotencyKey);
    }

    /**
     * @param  array<string, mixed>  $body
     */
    protected function change(string $path, array $body, ?string $idempotencyKey = null): ApiObject
    {
        return $this->client->patch($path, $body, $idempotencyKey);
    }
}
