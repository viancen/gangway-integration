<?php

namespace Gangway\Laravel\Http\Controllers;

use Gangway\Laravel\Events\WebhookReceived;
use Gangway\Laravel\Webhooks\VerifiedWebhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController
{
    public function __invoke(Request $request): JsonResponse
    {
        $webhook = $request->attributes->get('gangway_webhook');

        if (! $webhook instanceof VerifiedWebhook) {
            abort(401, 'Gangway webhook was not verified.');
        }

        WebhookReceived::dispatch($webhook);

        return response()->json([
            'received' => true,
            'delivery' => $webhook->deliveryId,
        ]);
    }
}
