<?php

namespace Gionin\Iterator;

use Gionin\Model;

/**
 * @implements \IteratorAggregate<int, array>
 */
class PaginatedIterator implements \IteratorAggregate
{
    public function __construct(
        private readonly Model $model,
        private readonly array $data,
        private readonly int $limit = 100,
    ) {
    }

    public function getIterator(): \Generator
    {
        $page = 1;

        do {
            $response = $this->model->find('all', $this->data, $page, $this->limit);

            if (!($response instanceof \Gionin\Response\PaginatedResponse)) {
                break;
            }

            foreach ($response->items as $item) {
                yield $item;
            }

            $hasMore = $response->hasNextPage();
            $page++;
        } while ($hasMore);
    }
}
