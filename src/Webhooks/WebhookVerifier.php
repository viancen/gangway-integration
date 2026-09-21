<?php

namespace Gangway\Laravel\Webhooks;

use Gangway\Laravel\Enums\WebhookEvent;
use Gangway\Laravel\Exceptions\ConfigurationException;
use Gangway\Laravel\Exceptions\WebhookSignatureException;
use Illuminate\Http\Request;

class WebhookVerifier
{
    public function __construct(
        protected string $secret,
        protected int $toleranceSeconds = 300,
    ) {}

    /**
     * Canonical HMAC used by Gangway when signing outbound deliveries.
     */
    public static function signature(string $secret, int $timestamp, string $body): string
    {
        return hash_hmac('sha256', $timestamp.'.'.$body, $secret);
    }

    public function verifyRequest(Request $request, ?string $secret = null): VerifiedWebhook
    {
        return $this->verify(
            $request->getContent(),
            (string) $request->header('Gangway-Signature', ''),
            (string) $request->header('Gangway-Event', ''),
            (string) $request->header('Gangway-Delivery', ''),
            $secret,
        );
    }

    public function verify(
        string $payload,
        string $signatureHeader,
        string $eventType = '',
        string $deliveryId = '',
        ?string $secret = null,
    ): VerifiedWebhook {
        $secret ??= $this->secret;

        if ($secret === '') {
            throw new ConfigurationException('Set GANGWAY_WEBHOOK_SECRET to the secret shown when the endpoint was created.');
        }

        [$timestamp, $signature] = $this->parseHeader($signatureHeader);

        if (abs(time() - $timestamp) > $this->toleranceSeconds) {
            throw new WebhookSignatureException('Gangway webhook timestamp is outside the allowed tolerance.');
        }

        $expected = self::signature($secret, $timestamp, $payload);

        if (! hash_equals($expected, $signature)) {
            throw new WebhookSignatureException('Gangway webhook signature is invalid.');
        }

        $decoded = json_decode($payload, true);

        if (! is_array($decoded)) {
            throw new WebhookSignatureException('Gangway webhook payload is not valid JSON.');
        }

        $type = $eventType !== '' ? $eventType : (string) ($decoded['type'] ?? '');

        return new VerifiedWebhook(
            type: $type,
            event: WebhookEvent::tryFrom($type),
            deliveryId: $deliveryId !== '' ? $deliveryId : (string) ($decoded['id'] ?? ''),
            timestamp: $timestamp,
            payload: $decoded,
        );
    }

    /**
     * @return array{0: int, 1: string}
     */
    private function parseHeader(string $header): array
    {
        $timestamp = null;
        $signature = null;

        foreach (explode(',', $header) as $part) {
            $part = trim($part);

            if (str_starts_with($part, 't=')) {
                $timestamp = (int) substr($part, 2);
            }

            if (str_starts_with($part, 'v1=')) {
                $signature = substr($part, 3);
            }
        }

        if ($timestamp === null || $timestamp < 1 || ! is_string($signature) || $signature === '') {
            throw new WebhookSignatureException('Gangway-Signature header is missing or malformed.');
        }

        return [$timestamp, $signature];
    }
}
