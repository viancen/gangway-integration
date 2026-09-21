<?php

namespace Gangway\Laravel\Resources;

use Gangway\Laravel\Resources\Concerns\InteractsWithClient;
use Gangway\Laravel\Responses\ApiObject;
use Gangway\Laravel\Responses\PaginatedResponse;

class Bookings
{
    use InteractsWithClient;

    /**
     * @param  array<string, mixed>  $query
     */
    public function all(array $query = []): PaginatedResponse
    {
        return $this->list('bookings', $query);
    }

    public function get(int|string $booking): ApiObject
    {
        return $this->find("bookings/{$booking}");
    }

    /**
     * Create a booking. Tours need `event_id`, rentals need `product_id` plus
     * a rental window, programmes need `program_run_id`.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes, ?string $idempotencyKey = null): ApiObject
    {
        return $this->client->post('bookings', $attributes, $idempotencyKey);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(int|string $booking, array $attributes, ?string $idempotencyKey = null): ApiObject
    {
        return $this->change("bookings/{$booking}", $attributes, $idempotencyKey);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function cancel(int|string $booking, array $attributes = [], ?string $idempotencyKey = null): ApiObject
    {
        return $this->client->post("bookings/{$booking}/cancel", $attributes, $idempotencyKey);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function reschedule(int|string $booking, array $attributes, ?string $idempotencyKey = null): ApiObject
    {
        return $this->client->post("bookings/{$booking}/reschedule", $attributes, $idempotencyKey);
    }
}
