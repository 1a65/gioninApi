<?php

namespace Gionin\Response;

class ApiResponse
{
    public function __construct(
        public readonly int $statusCode,
        public readonly array|null $data,
        public readonly string $rawBody,
    ) {
    }

    public function isSuccessful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }
}
