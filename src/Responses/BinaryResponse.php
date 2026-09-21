<?php

namespace Gangway\Laravel\Responses;

class BinaryResponse
{
    public function __construct(
        public readonly string $contents,
        public readonly string $contentType,
        public readonly ?string $filename = null,
        public readonly int $status = 200,
    ) {}

    public function isPdf(): bool
    {
        return str_contains($this->contentType, 'pdf');
    }

    public function isXml(): bool
    {
        return str_contains($this->contentType, 'xml');
    }
}
