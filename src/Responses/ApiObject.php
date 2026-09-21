<?php

namespace Gangway\Laravel\Responses;

use ArrayAccess;
use Illuminate\Support\Arr;
use JsonSerializable;

/**
 * A single Gangway resource from the `{ "data": { ... } }` envelope.
 *
 * @implements ArrayAccess<string, mixed>
 */
class ApiObject implements ArrayAccess, JsonSerializable
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(public readonly array $attributes) {}

    public function id(): int|string|null
    {
        $id = $this->attributes['id'] ?? null;

        return is_int($id) || is_string($id) ? $id : null;
    }

    public function object(): ?string
    {
        $object = $this->attributes['object'] ?? null;

        return is_string($object) ? $object : null;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->attributes, $key, $default);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    public function jsonSerialize(): array
    {
        return $this->attributes;
    }

    public function offsetExists(mixed $offset): bool
    {
        return Arr::has($this->attributes, $offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return Arr::get($this->attributes, $offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \LogicException('Gangway API objects are immutable.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new \LogicException('Gangway API objects are immutable.');
    }

    public function __get(string $name): mixed
    {
        return $this->get($name);
    }

    public function __isset(string $name): bool
    {
        return $this->offsetExists($name);
    }
}
