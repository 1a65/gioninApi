<?php

namespace Gionin\Response;

class PaginatedResponse extends ApiResponse
{
    public readonly int $total;
    public readonly array $items;

    public function __construct(
        int $statusCode,
        string $rawBody,
        int $total,
        array $items,
        int $page,
        int $limit,
    ) {
        parent::__construct($statusCode, $items, $rawBody);
        $this->total = $total;
        $this->items = $items;
        $this->page = $page;
        $this->limit = $limit;
    }

    public readonly int $page;
    public readonly int $limit;

    public function hasNextPage(): bool
    {
        return ($this->page * $this->limit) < $this->total;
    }
}
