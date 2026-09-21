<?php

namespace Gangway\Laravel\Resources;

use Gangway\Laravel\Resources\Concerns\InteractsWithClient;
use Gangway\Laravel\Responses\ApiObject;
use Gangway\Laravel\Responses\PaginatedResponse;

class Tickets
{
    use InteractsWithClient;

    /**
     * @param  array<string, mixed>  $query
     */
    public function all(array $query = []): PaginatedResponse
    {
        return $this->list('tickets', $query);
    }

    public function get(int|string $ticket): ApiObject
    {
        return $this->find("tickets/{$ticket}");
    }

    public function lookup(string $qrCode): ApiObject
    {
        return $this->find('tickets/lookup', ['qr_code' => $qrCode]);
    }

    public function eligibility(int|string $ticket): ApiObject
    {
        return $this->find("tickets/{$ticket}/eligibility");
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(int|string $ticket, array $attributes, ?string $idempotencyKey = null): ApiObject
    {
        return $this->change("tickets/{$ticket}", $attributes, $idempotencyKey);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function cancel(int|string $ticket, array $attributes = [], ?string $idempotencyKey = null): ApiObject
    {
        return $this->client->post("tickets/{$ticket}/cancel", $attributes, $idempotencyKey);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function checkIn(int|string $ticket, array $attributes = [], ?string $idempotencyKey = null): ApiObject
    {
        return $this->client->post("tickets/{$ticket}/check-in", $attributes, $idempotencyKey);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function checkOut(int|string $ticket, array $attributes = [], ?string $idempotencyKey = null): ApiObject
    {
        return $this->client->post("tickets/{$ticket}/check-out", $attributes, $idempotencyKey);
    }
}
