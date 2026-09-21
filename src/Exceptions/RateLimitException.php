<?php

namespace Gangway\Laravel\Exceptions;

class RateLimitException extends GangwayException
{
    public function __construct(
        string $message,
        string $errorType = 'rate_limit_exceeded',
        int $status = 429,
        ?string $requestId = null,
        array $details = [],
        ?string $documentationUrl = null,
        ?\Throwable $previous = null,
        public readonly ?int $retryAfter = null,
    ) {
        parent::__construct($message, $errorType, $status, $requestId, $details, $documentationUrl, $previous);
    }
}
