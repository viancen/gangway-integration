<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Gangway instance
    |--------------------------------------------------------------------------
    |
    | Base URL of the Gangway environment this Laravel app talks to.
    | Do not include a trailing slash or the /api/v1 prefix.
    |
    */

    'base_url' => env('GANGWAY_BASE_URL', 'https://gangway.app'),

    /*
    |--------------------------------------------------------------------------
    | Operator API key
    |--------------------------------------------------------------------------
    |
    | Issued from Gangway → Dashboard → Integrations. Keys start with `gw_`
    | and are scoped per operator. Prefer a key that only has the scopes
    | this website actually needs.
    |
    */

    'api_key' => env('GANGWAY_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Webhook signing secret
    |--------------------------------------------------------------------------
    |
    | Shown once when a webhook endpoint is created or its secret is rotated.
    | Secrets start with `whsec_`. Used by the gangway.webhook middleware.
    |
    */

    'webhook_secret' => env('GANGWAY_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | HTTP client
    |--------------------------------------------------------------------------
    */

    'timeout' => (int) env('GANGWAY_TIMEOUT', 30),

    'retry' => [
        'times' => (int) env('GANGWAY_RETRY_TIMES', 2),
        'sleep_ms' => (int) env('GANGWAY_RETRY_SLEEP', 200),
    ],

    'user_agent' => env('GANGWAY_USER_AGENT', 'gangway-laravel/1.0.0'),

    /*
    |--------------------------------------------------------------------------
    | Idempotency
    |--------------------------------------------------------------------------
    |
    | When enabled, every POST/PUT/PATCH automatically sends an Idempotency-Key
    | unless the caller already provided one. Matches Gangway's write contract.
    |
    */

    'idempotency' => [
        'auto' => (bool) env('GANGWAY_IDEMPOTENCY_AUTO', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Incoming webhooks
    |--------------------------------------------------------------------------
    |
    | When enabled the service provider registers POST {path} and verifies
    | the Gangway-Signature header before dispatching WebhookReceived.
    |
    */

    'webhook' => [
        'enabled' => (bool) env('GANGWAY_WEBHOOK_ROUTE_ENABLED', false),
        'path' => env('GANGWAY_WEBHOOK_PATH', 'webhooks/gangway'),
        'middleware' => ['api', 'gangway.webhook'],
        'tolerance_seconds' => (int) env('GANGWAY_WEBHOOK_TOLERANCE', 300),
    ],

];
