<?php

namespace Gangway\Laravel\Events;

use Gangway\Laravel\Enums\WebhookEvent;
use Gangway\Laravel\Webhooks\VerifiedWebhook;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebhookReceived
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly VerifiedWebhook $webhook) {}

    public function type(): string
    {
        return $this->webhook->type;
    }

    public function event(): ?WebhookEvent
    {
        return $this->webhook->event;
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->webhook->payload;
    }
}
