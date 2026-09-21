<?php

namespace Gangway\Laravel\Responses;

class HttpMeta
{
    public function __construct(
        public readonly int $status,
        public readonly ?string $requestId = null,
        public readonly ?string $operator = null,
        public readonly ?int $rateLimit = null,
        public readonly ?int $rateLimitRemaining = null,
        public readonly ?int $rateLimitReset = null,
        public readonly bool $idempotencyReplayed = false,
    ) {}

    /**
     * @param  array<string, array<int, string>>  $headers
     */
    public static function fromHeaders(int $status, array $headers): self
    {
        return new self(
            status: $status,
            requestId: self::header($headers, 'Gangway-Request-Id'),
            operator: self::header($headers, 'Gangway-Operator'),
            rateLimit: self::intHeader($headers, 'X-RateLimit-Limit'),
            rateLimitRemaining: self::intHeader($headers, 'X-RateLimit-Remaining'),
            rateLimitReset: self::intHeader($headers, 'X-RateLimit-Reset'),
            idempotencyReplayed: strtolower((string) self::header($headers, 'Idempotency-Replayed')) === 'true',
        );
    }

    /**
     * @param  array<string, array<int, string>>  $headers
     */
    private static function header(array $headers, string $name): ?string
    {
        foreach ($headers as $key => $values) {
            if (strcasecmp((string) $key, $name) === 0) {
                $value = $values[0] ?? null;

                return is_string($value) && $value !== '' ? $value : null;
            }
        }

        return null;
    }

    /**
     * @param  array<string, array<int, string>>  $headers
     */
    private static function intHeader(array $headers, string $name): ?int
    {
        $value = self::header($headers, $name);

        return $value !== null && is_numeric($value) ? (int) $value : null;
    }
}
