<?php

namespace Gangway\Laravel\Resources;

use Gangway\Laravel\Resources\Concerns\InteractsWithClient;
use Gangway\Laravel\Responses\ApiObject;
use Gangway\Laravel\Responses\PaginatedResponse;

class Waivers
{
    use InteractsWithClient;

    /**
     * @param  array<string, mixed>  $query
     */
    public function all(array $query = []): PaginatedResponse
    {
        return $this->list('waivers', $query);
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public function acceptances(array $query = []): PaginatedResponse
    {
        return $this->list('waivers/acceptances', $query);
    }

    public function get(int|string $waiver): ApiObject
    {
        return $this->find("waivers/{$waiver}");
    }
}
