<?php

namespace Gangway\Laravel\Responses;

use ArrayIterator;
use Gangway\Laravel\Client;
use IteratorAggregate;
use Traversable;

/**
 * A Gangway list envelope: `{ data, meta, links }`.
 *
 * @implements IteratorAggregate<int, ApiObject>
 */
class PaginatedResponse implements IteratorAggregate
{
    /**
     * @param  list<ApiObject>  $items
     * @param  array<string, mixed>  $meta
     * @param  array<string, mixed>  $links
     * @param  array<string, mixed>  $query
     */
    public function __construct(
        public readonly array $items,
        public readonly array $meta,
        public readonly array $links,
        private readonly Client $client,
        private readonly string $path,
        private readonly array $query = [],
        private readonly string $prefix = 'v1',
    ) {}

    /**
     * @return list<ApiObject>
     */
    public function items(): array
    {
        return $this->items;
    }

    public function total(): int
    {
        return (int) ($this->meta['total'] ?? count($this->items));
    }

    public function perPage(): int
    {
        return (int) ($this->meta['per_page'] ?? count($this->items));
    }

    public function currentPage(): int
    {
        return (int) ($this->meta['current_page'] ?? 1);
    }

    public function lastPage(): int
    {
        return (int) ($this->meta['last_page'] ?? 1);
    }

    public function hasMore(): bool
    {
        return (bool) ($this->meta['has_more'] ?? false);
    }

    public function nextPage(): ?self
    {
        if (! $this->hasMore()) {
            return null;
        }

        return $this->client->getPaginated(
            $this->path,
            [...$this->query, 'page' => $this->currentPage() + 1],
            $this->prefix,
        );
    }

    /**
     * Walk every remaining page, including this one.
     *
     * @return \Generator<int, ApiObject>
     */
    public function autoPaging(): \Generator
    {
        $page = $this;

        while ($page instanceof self) {
            foreach ($page->items as $item) {
                yield $item;
            }

            $page = $page->nextPage();
        }
    }

    /**
     * @return array{data: list<array<string, mixed>>, meta: array<string, mixed>, links: array<string, mixed>}
     */
    public function toArray(): array
    {
        return [
            'data' => array_map(static fn (ApiObject $item): array => $item->toArray(), $this->items),
            'meta' => $this->meta,
            'links' => $this->links,
        ];
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }
}
