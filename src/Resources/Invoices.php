<?php

namespace Gangway\Laravel\Resources;

use Gangway\Laravel\Resources\Concerns\InteractsWithClient;
use Gangway\Laravel\Responses\ApiObject;
use Gangway\Laravel\Responses\BinaryResponse;
use Gangway\Laravel\Responses\PaginatedResponse;

class Invoices
{
    use InteractsWithClient;

    /**
     * @param  array<string, mixed>  $query
     */
    public function all(array $query = []): PaginatedResponse
    {
        return $this->list('invoices', $query);
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public function revenue(array $query = []): ApiObject
    {
        return $this->find('invoices/revenue', $query);
    }

    public function get(int|string $invoice): ApiObject
    {
        return $this->find("invoices/{$invoice}");
    }

    public function pdf(int|string $invoice): BinaryResponse
    {
        return $this->client->download("invoices/{$invoice}/pdf");
    }

    public function ubl(int|string $invoice): BinaryResponse
    {
        return $this->client->download("invoices/{$invoice}/ubl.xml");
    }
}
