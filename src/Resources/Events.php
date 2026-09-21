<?php

namespace Gangway\Laravel\Resources;

use Gangway\Laravel\Resources\Concerns\InteractsWithClient;
use Gangway\Laravel\Responses\ApiObject;
use Gangway\Laravel\Responses\PaginatedResponse;

class Events
{
    use InteractsWithClient;

    /**
     * @param  array<string, mixed>  $query
     */
    public function all(array $query = []): PaginatedResponse
    {
        return $this->list('events', $query);
    }

    public function get(int|string $event): ApiObject
    {
        return $this->find("events/{$event}");
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes, ?string $idempotencyKey = null): ApiObject
    {
        return $this->client->post('events', $attributes, $idempotencyKey);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(int|string $event, array $attributes, ?string $idempotencyKey = null): ApiObject
    {
        return $this->change("events/{$event}", $attributes, $idempotencyKey);
    }

    public function deactivate(int|string $event, ?string $idempotencyKey = null): ApiObject
    {
        return $this->client->post("events/{$event}/deactivate", [], $idempotencyKey);
    }
}
