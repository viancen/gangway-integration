<?php

namespace Gangway\Laravel\Resources;

use Gangway\Laravel\Resources\Concerns\InteractsWithClient;
use Gangway\Laravel\Responses\ApiObject;
use Gangway\Laravel\Responses\PaginatedResponse;

class Availability
{
    use InteractsWithClient;

    /**
     * @param  array<string, mixed>  $query
     */
    public function tours(array $query = []): PaginatedResponse
    {
        return $this->list('availability/tours', $query);
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public function rentals(array $query = []): ApiObject
    {
        return $this->find('availability/rentals', $query);
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public function rentalCalendar(array $query = []): PaginatedResponse
    {
        return $this->list('availability/rentals/calendar', $query);
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public function programs(array $query = []): ApiObject
    {
        return $this->find('availability/programs', $query);
    }
}
