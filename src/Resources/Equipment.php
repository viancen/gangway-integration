<?php

namespace Gangway\Laravel\Resources;

use Gangway\Laravel\Resources\Concerns\InteractsWithClient;
use Gangway\Laravel\Responses\ApiObject;
use Gangway\Laravel\Responses\PaginatedResponse;

class Equipment
{
    use InteractsWithClient;

    /**
     * @param  array<string, mixed>  $query
     */
    public function all(array $query = []): PaginatedResponse
    {
        return $this->list('equipment', $query);
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public function groups(array $query = []): PaginatedResponse
    {
        return $this->list('equipment/groups', $query);
    }

    public function get(int|string $item): ApiObject
    {
        return $this->find("equipment/{$item}");
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes, ?string $idempotencyKey = null): ApiObject
    {
        return $this->client->post('equipment', $attributes, $idempotencyKey);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(int|string $item, array $attributes, ?string $idempotencyKey = null): ApiObject
    {
        return $this->change("equipment/{$item}", $attributes, $idempotencyKey);
    }
}
