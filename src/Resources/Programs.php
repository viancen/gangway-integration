<?php

namespace Gangway\Laravel\Resources;

use Gangway\Laravel\Resources\Concerns\InteractsWithClient;
use Gangway\Laravel\Responses\ApiObject;
use Gangway\Laravel\Responses\PaginatedResponse;

class Programs
{
    use InteractsWithClient;

    /**
     * @param  array<string, mixed>  $query
     */
    public function runs(array $query = []): PaginatedResponse
    {
        return $this->list('programs/runs', $query);
    }

    public function run(int|string $run): ApiObject
    {
        return $this->find("programs/runs/{$run}");
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createRun(array $attributes, ?string $idempotencyKey = null): ApiObject
    {
        return $this->client->post('programs/runs', $attributes, $idempotencyKey);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateRun(int|string $run, array $attributes, ?string $idempotencyKey = null): ApiObject
    {
        return $this->change("programs/runs/{$run}", $attributes, $idempotencyKey);
    }

    /**
     * @param  array<string, mixed>  $sessions
     */
    public function replaceSessions(int|string $run, array $sessions, ?string $idempotencyKey = null): ApiObject
    {
        return $this->client->put("programs/runs/{$run}/sessions", $sessions, $idempotencyKey);
    }

    public function confirm(int|string $run, ?string $idempotencyKey = null): ApiObject
    {
        return $this->client->post("programs/runs/{$run}/confirm", [], $idempotencyKey);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function cancel(int|string $run, array $attributes = [], ?string $idempotencyKey = null): ApiObject
    {
        return $this->client->post("programs/runs/{$run}/cancel", $attributes, $idempotencyKey);
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public function participants(int|string $run, array $query = []): PaginatedResponse
    {
        return $this->list("programs/runs/{$run}/participants", $query);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function recordAttendance(int|string $run, array $attributes, ?string $idempotencyKey = null): ApiObject
    {
        return $this->client->post("programs/runs/{$run}/attendance", $attributes, $idempotencyKey);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateCompletion(int|string $run, int|string $ticket, array $attributes, ?string $idempotencyKey = null): ApiObject
    {
        return $this->change("programs/runs/{$run}/participants/{$ticket}/completion", $attributes, $idempotencyKey);
    }
}
