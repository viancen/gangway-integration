<?php

namespace Gangway\Laravel;

use Gangway\Laravel\Exceptions\ConfigurationException;
use Gangway\Laravel\Exceptions\GangwayException;
use Gangway\Laravel\Exceptions\RateLimitException;
use Gangway\Laravel\Responses\ApiObject;
use Gangway\Laravel\Responses\BinaryResponse;
use Gangway\Laravel\Responses\HttpMeta;
use Gangway\Laravel\Responses\PaginatedResponse;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Client
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(protected array $config) {}

    /**
     * Clone the client with a different operator key and/or instance URL.
     */
    public function withCredentials(?string $apiKey = null, ?string $baseUrl = null): self
    {
        $clone = clone $this;
        $clone->config = [
            ...$this->config,
            'api_key' => $apiKey ?? $this->config['api_key'] ?? null,
            'base_url' => $baseUrl ?? $this->config['base_url'] ?? null,
        ];

        return $clone;
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public function get(string $path, array $query = [], string $prefix = 'v1'): ApiObject
    {
        return $this->toObject($this->send('GET', $path, ['query' => $query], $prefix));
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public function getPaginated(string $path, array $query = [], string $prefix = 'v1'): PaginatedResponse
    {
        $response = $this->send('GET', $path, ['query' => $query], $prefix);
        $payload = $this->json($response);

        $items = [];
        foreach ($payload['data'] ?? [] as $item) {
            if (is_array($item)) {
                $items[] = new ApiObject($item);
            }
        }

        return new PaginatedResponse(
            items: $items,
            meta: is_array($payload['meta'] ?? null) ? $payload['meta'] : [],
            links: is_array($payload['links'] ?? null) ? $payload['links'] : [],
            client: $this,
            path: $path,
            query: $query,
            prefix: $prefix,
        );
    }

    /**
     * @param  array<string, mixed>  $body
     */
    public function post(string $path, array $body = [], ?string $idempotencyKey = null, string $prefix = 'v1'): ApiObject
    {
        return $this->toObject($this->send('POST', $path, [
            'json' => $body,
            'idempotency_key' => $idempotencyKey,
        ], $prefix));
    }

    /**
     * @param  array<string, mixed>  $body
     */
    public function patch(string $path, array $body = [], ?string $idempotencyKey = null, string $prefix = 'v1'): ApiObject
    {
        return $this->toObject($this->send('PATCH', $path, [
            'json' => $body,
            'idempotency_key' => $idempotencyKey,
        ], $prefix));
    }

    /**
     * @param  array<string, mixed>  $body
     */
    public function put(string $path, array $body = [], ?string $idempotencyKey = null, string $prefix = 'v1'): ApiObject
    {
        return $this->toObject($this->send('PUT', $path, [
            'json' => $body,
            'idempotency_key' => $idempotencyKey,
        ], $prefix));
    }

    public function delete(string $path, string $prefix = 'v1'): ApiObject|true
    {
        $response = $this->send('DELETE', $path, [], $prefix);

        if ($response->status() === 204 || $response->body() === '') {
            return true;
        }

        $payload = $this->json($response);

        return isset($payload['data']) && is_array($payload['data'])
            ? new ApiObject($payload['data'])
            : true;
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public function download(string $path, array $query = [], string $prefix = 'v1'): BinaryResponse
    {
        $response = $this->send('GET', $path, [
            'query' => $query,
            'binary' => true,
        ], $prefix);

        $disposition = $response->header('Content-Disposition') ?? '';
        $filename = null;

        if (preg_match('/filename="?([^"]+)"?/i', $disposition, $matches) === 1) {
            $filename = $matches[1];
        }

        return new BinaryResponse(
            contents: $response->body(),
            contentType: $response->header('Content-Type') ?: 'application/octet-stream',
            filename: $filename,
            status: $response->status(),
        );
    }

    /**
     * Last successful response metadata (request id, rate limits, operator slug).
     */
    public function lastMeta(): ?HttpMeta
    {
        return $this->config['_last_meta'] ?? null;
    }

    /**
     * @param  array{query?: array<string, mixed>, json?: array<string, mixed>, idempotency_key?: ?string, binary?: bool, authenticate?: bool}  $options
     */
    public function send(string $method, string $path, array $options = [], string $prefix = 'v1'): Response
    {
        $this->assertConfigured($options['authenticate'] ?? true);

        $url = $this->url($path, $prefix);
        $request = $this->pendingRequest($method, $options['idempotency_key'] ?? null);

        if (! empty($options['query'])) {
            $request = $request->withQueryParameters($this->stringifyQuery($options['query']));
        }

        $response = match (strtoupper($method)) {
            'GET' => $request->get($url),
            'POST' => $request->post($url, $options['json'] ?? []),
            'PATCH' => $request->patch($url, $options['json'] ?? []),
            'PUT' => $request->put($url, $options['json'] ?? []),
            'DELETE' => $request->delete($url, $options['json'] ?? []),
            default => throw new ConfigurationException("Unsupported HTTP method [{$method}]."),
        };

        $this->config['_last_meta'] = HttpMeta::fromHeaders($response->status(), $response->headers());

        if ($response->successful()) {
            return $response;
        }

        $this->throwForFailed($response);

        return $response;
    }

    public function baseUrl(): string
    {
        return rtrim((string) ($this->config['base_url'] ?? ''), '/');
    }

    public function apiKey(): ?string
    {
        $key = $this->config['api_key'] ?? null;

        return is_string($key) && $key !== '' ? $key : null;
    }

    private function toObject(Response $response): ApiObject
    {
        $payload = $this->json($response);
        $data = $payload['data'] ?? $payload;

        if (! is_array($data)) {
            throw new GangwayException('Gangway returned a response without a data object.');
        }

        return new ApiObject($data);
    }

    /**
     * @return array<string, mixed>
     */
    private function json(Response $response): array
    {
        $payload = $response->json();

        return is_array($payload) ? $payload : [];
    }

    private function throwForFailed(Response $response): never
    {
        $payload = $this->json($response);
        $exception = GangwayException::fromResponse($response->status(), $payload);

        if ($exception instanceof RateLimitException) {
            $retryAfter = $response->header('Retry-After');

            throw new RateLimitException(
                $exception->getMessage(),
                $exception->errorType,
                $exception->status,
                $exception->requestId,
                $exception->details,
                $exception->documentationUrl,
                retryAfter: is_numeric($retryAfter) ? (int) $retryAfter : null,
            );
        }

        throw $exception;
    }

    private function pendingRequest(string $method, ?string $idempotencyKey): PendingRequest
    {
        $headers = [
            'Accept' => 'application/json',
            'User-Agent' => (string) ($this->config['user_agent'] ?? 'gangway-laravel/1.0.0'),
        ];

        if ($this->apiKey() !== null) {
            $headers['Authorization'] = 'Bearer '.$this->apiKey();
        }

        if ($this->shouldSendIdempotencyKey($method)) {
            $headers['Idempotency-Key'] = $idempotencyKey ?: (string) Str::uuid();
        } elseif (is_string($idempotencyKey) && $idempotencyKey !== '') {
            $headers['Idempotency-Key'] = $idempotencyKey;
        }

        $request = Http::withHeaders($headers)
            ->acceptJson()
            ->asJson()
            ->timeout((int) ($this->config['timeout'] ?? 30));

        $retries = (int) ($this->config['retry']['times'] ?? 0);

        if ($retries > 0) {
            $request = $request->retry(
                $retries,
                (int) ($this->config['retry']['sleep_ms'] ?? 200),
                throw: false,
            );
        }

        return $request;
    }

    private function shouldSendIdempotencyKey(string $method): bool
    {
        if (! in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'], true)) {
            return false;
        }

        return (bool) ($this->config['idempotency']['auto'] ?? true);
    }

    private function url(string $path, string $prefix): string
    {
        $path = ltrim($path, '/');

        if ($prefix === '') {
            return $this->baseUrl().'/'.ltrim($path, '/');
        }

        return $this->baseUrl().'/api/'.trim($prefix, '/').'/'.$path;
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    private function stringifyQuery(array $query): array
    {
        return $query;
    }

    private function assertConfigured(bool $requiresKey = true): void
    {
        if ($this->baseUrl() === '') {
            throw new ConfigurationException('Set GANGWAY_BASE_URL or publish config/gangway.php.');
        }

        if ($requiresKey && $this->apiKey() === null) {
            throw new ConfigurationException('Set GANGWAY_API_KEY to an operator key issued in Gangway → Integrations.');
        }
    }
}
