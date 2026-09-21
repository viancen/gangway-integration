<?php

namespace Gangway\Laravel\Resources;

use Gangway\Laravel\Client;
use Gangway\Laravel\Responses\ApiObject;

/**
 * Public widget + WordPress helper endpoints (not under /api/v1).
 */
class Widgets
{
    public function __construct(protected Client $client) {}

    /**
     * Widgets for this operator, same payload the WordPress plugin uses.
     *
     * @return array{widgets: list<array<string, mixed>>, app_url: ?string}
     */
    public function all(): array
    {
        $response = $this->client->send('GET', 'api/wordpress/widgets', [], '');
        $payload = $response->json();

        return [
            'widgets' => is_array($payload['widgets'] ?? null) ? $payload['widgets'] : [],
            'app_url' => isset($payload['app_url']) ? (string) $payload['app_url'] : null,
        ];
    }

    public function data(string $token): ApiObject
    {
        $response = $this->client->send('GET', 'api/widget/'.rawurlencode($token), [
            'authenticate' => false,
        ], '');
        $payload = $response->json();

        return new ApiObject(is_array($payload) ? $payload : []);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function book(string $token, array $attributes): ApiObject
    {
        $response = $this->client->send('POST', 'api/widget/'.rawurlencode($token).'/book', [
            'json' => $attributes,
            'authenticate' => false,
        ], '');
        $payload = $response->json();

        return new ApiObject(is_array($payload) ? $payload : []);
    }
}
