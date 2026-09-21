<?php

namespace Gangway\Laravel\Http\Middleware;

use Closure;
use Gangway\Laravel\Exceptions\WebhookSignatureException;
use Gangway\Laravel\Facades\Gangway;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyGangwayWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $request->attributes->set(
                'gangway_webhook',
                Gangway::verifier()->verifyRequest($request),
            );
        } catch (WebhookSignatureException $exception) {
            abort(401, $exception->getMessage());
        }

        return $next($request);
    }
}
