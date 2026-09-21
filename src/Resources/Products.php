<?php

namespace Gangway\Laravel\Resources;

use Gangway\Laravel\Resources\Concerns\InteractsWithClient;
use Gangway\Laravel\Responses\ApiObject;
use Gangway\Laravel\Responses\PaginatedResponse;

class Products
{
    use InteractsWithClient;

    /**
     * @param  array<string, mixed>  $query
     */
    public function all(array $query = []): PaginatedResponse
    {
        return $this->list('products', $query);
    }

    public function get(int|string $product): ApiObject
    {
        return $this->find("products/{$product}");
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes, ?string $idempotencyKey = null): ApiObject
    {
        return $this->client->post('products', $attributes, $idempotencyKey);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(int|string $product, array $attributes, ?string $idempotencyKey = null): ApiObject
    {
        return $this->change("products/{$product}", $attributes, $idempotencyKey);
    }

    public function deactivate(int|string $product, ?string $idempotencyKey = null): ApiObject
    {
        return $this->client->post("products/{$product}/deactivate", [], $idempotencyKey);
    }
}
