<?php

namespace Gangway\Laravel\Exceptions;

class WebhookSignatureException extends GangwayException
{
    public function __construct(string $message)
    {
        parent::__construct($message, 'webhook_signature_invalid', 401);
    }
}
