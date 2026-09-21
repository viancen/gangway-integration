<?php

namespace Gangway\Laravel\Webhooks;

use Gangway\Laravel\Enums\WebhookEvent;
use Gangway\Laravel\Responses\ApiObject;

class VerifiedWebhook
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public readonly string $type,
        public readonly ?WebhookEvent $event,
        public readonly string $deliveryId,
        public readonly int $timestamp,
        public readonly array $payload,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function data(): array
    {
        return is_array($this->payload['data'] ?? null) ? $this->payload['data'] : [];
    }

    public function object(): ?ApiObject
    {
        $data = $this->data();
        $object = $data['object'] ?? $data;

        return is_array($object) ? new ApiObject($object) : null;
    }

    public function is(WebhookEvent|string $event): bool
    {
        $value = $event instanceof WebhookEvent ? $event->value : $event;

        return $this->type === $value;
    }
}
