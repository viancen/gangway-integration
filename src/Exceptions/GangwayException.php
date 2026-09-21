<?php

namespace Gangway\Laravel\Exceptions;

use RuntimeException;
use Throwable;

class GangwayException extends RuntimeException
{
    /**
     * @param  array<string, mixed>  $details
     */
    public function __construct(
        string $message,
        public readonly string $errorType = 'unknown',
        public readonly int $status = 0,
        public readonly ?string $requestId = null,
        public readonly array $details = [],
        public readonly ?string $documentationUrl = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $status, $previous);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromResponse(int $status, array $payload, ?Throwable $previous = null): self
    {
        $error = is_array($payload['error'] ?? null) ? $payload['error'] : [];
        $type = (string) ($error['type'] ?? 'unknown');
        $message = (string) ($error['message'] ?? $payload['message'] ?? 'Unexpected Gangway API error.');
        $details = is_array($error['details'] ?? null) ? $error['details'] : [];

        $class = match ($type) {
            'unauthorized' => AuthenticationException::class,
            'insufficient_scope' => InsufficientScopeException::class,
            'rate_limit_exceeded' => RateLimitException::class,
            'validation_failed' => ValidationException::class,
            'unprocessable_entity' => UnprocessableException::class,
            'resource_not_found' => NotFoundException::class,
            'conflict' => ConflictException::class,
            'checkin_blocked' => CheckinBlockedException::class,
            'idempotency_key_reuse', 'idempotency_in_progress' => IdempotencyException::class,
            'forbidden' => ForbiddenException::class,
            default => self::class,
        };

        return new $class(
            $message,
            $type,
            (int) ($error['status'] ?? $status),
            isset($error['request_id']) ? (string) $error['request_id'] : null,
            $details,
            isset($error['documentation_url']) ? (string) $error['documentation_url'] : null,
            $previous,
        );
    }
}
