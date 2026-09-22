<?php

namespace Gangway\Laravel;

use Gangway\Laravel\Resources\Availability;
use Gangway\Laravel\Resources\Bookings;
use Gangway\Laravel\Resources\Cms;
use Gangway\Laravel\Resources\Customers;
use Gangway\Laravel\Resources\Discovery;
use Gangway\Laravel\Resources\Equipment;
use Gangway\Laravel\Resources\Events;
use Gangway\Laravel\Resources\Invoices;
use Gangway\Laravel\Resources\Products;
use Gangway\Laravel\Resources\Programs;
use Gangway\Laravel\Resources\Tickets;
use Gangway\Laravel\Resources\Waivers;
use Gangway\Laravel\Resources\Webhooks;
use Gangway\Laravel\Resources\Widgets;
use Gangway\Laravel\Responses\ApiObject;
use Gangway\Laravel\Responses\HttpMeta;
use Gangway\Laravel\Webhooks\WebhookVerifier;

class GangwayManager
{
    public function __construct(protected Client $client) {}

    /**
     * Talk to a different Gangway instance or operator key for this call chain.
     */
    public function using(?string $apiKey = null, ?string $baseUrl = null): self
    {
        return new self($this->client->withCredentials($apiKey, $baseUrl));
    }

    public function client(): Client
    {
        return $this->client;
    }

    public function lastMeta(): ?HttpMeta
    {
        return $this->client->lastMeta();
    }

    public function ping(): ApiObject
    {
        return $this->discovery()->ping();
    }

    public function me(): ApiObject
    {
        return $this->discovery()->me();
    }

    public function capabilities(): ApiObject
    {
        return $this->discovery()->capabilities();
    }

    public function discovery(): Discovery
    {
        return new Discovery($this->client);
    }

    public function products(): Products
    {
        return new Products($this->client);
    }

    public function events(): Events
    {
        return new Events($this->client);
    }

    public function programs(): Programs
    {
        return new Programs($this->client);
    }

    public function availability(): Availability
    {
        return new Availability($this->client);
    }

    public function bookings(): Bookings
    {
        return new Bookings($this->client);
    }

    public function tickets(): Tickets
    {
        return new Tickets($this->client);
    }

    public function equipment(): Equipment
    {
        return new Equipment($this->client);
    }

    public function invoices(): Invoices
    {
        return new Invoices($this->client);
    }

    public function customers(): Customers
    {
        return new Customers($this->client);
    }

    public function waivers(): Waivers
    {
        return new Waivers($this->client);
    }

    public function webhooks(): Webhooks
    {
        return new Webhooks($this->client);
    }

    public function widgets(): Widgets
    {
        return new Widgets($this->client);
    }

    public function cms(): Cms
    {
        return new Cms($this->client);
    }

    public function verifier(?string $secret = null): WebhookVerifier
    {
        return new WebhookVerifier(
            $secret ?? (string) config('gangway.webhook_secret', ''),
            (int) config('gangway.webhook.tolerance_seconds', 300),
        );
    }

    public function widgetScriptUrl(): string
    {
        return $this->client->baseUrl().'/widget.js';
    }

    public function widgetEmbedUrl(string $token): string
    {
        return $this->client->baseUrl().'/w/'.rawurlencode($token).'?embedded=1';
    }
}
