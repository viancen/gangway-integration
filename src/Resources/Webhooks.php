<?php

namespace Gangway\Laravel\Resources;

use Gangway\Laravel\Resources\Concerns\InteractsWithClient;
use Gangway\Laravel\Responses\ApiObject;
use Gangway\Laravel\Responses\PaginatedResponse;

class Webhooks
{
    use InteractsWithClient;

    /**
     * @param  array<string, mixed>  $query
     */
    public function all(array $query = []): PaginatedResponse
    {
        return $this->list('webhooks', $query);
    }

    public function get(int|string $endpoint): ApiObject
    {
        return $this->find("webhooks/{$endpoint}");
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes, ?string $idempotencyKey = null): ApiObject
    {
        return $this->client->post('webhooks', $attributes, $idempotencyKey);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(int|string $endpoint, array $attributes, ?string $idempotencyKey = null): ApiObject
    {
        return $this->change("webhooks/{$endpoint}", $attributes, $idempotencyKey);
    }

    public function delete(int|string $endpoint): ApiObject|true
    {
        return $this->client->delete("webhooks/{$endpoint}");
    }

    public function rotateSecret(int|string $endpoint, ?string $idempotencyKey = null): ApiObject
    {
        return $this->client->post("webhooks/{$endpoint}/rotate-secret", [], $idempotencyKey);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function test(int|string $endpoint, array $attributes = [], ?string $idempotencyKey = null): ApiObject
    {
        return $this->client->post("webhooks/{$endpoint}/test", $attributes, $idempotencyKey);
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public function deliveries(array $query = []): PaginatedResponse
    {
        return $this->list('webhooks/deliveries', $query);
    }

    public function retryDelivery(int|string $delivery, ?string $idempotencyKey = null): ApiObject
    {
        return $this->client->post("webhooks/deliveries/{$delivery}/retry", [], $idempotencyKey);
    }
}
