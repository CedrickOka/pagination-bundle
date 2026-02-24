<?php

declare(strict_types=1);

namespace Oka\PaginationBundle\Pagination;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class Page
{
    private int $pageNumber;

    public function __construct(
        private readonly int $page,
        private readonly int $itemPerPage,
        private readonly array $filters,
        private readonly array $orderBy,
        private readonly int $itemOffset,
        private readonly int $fullyItems,
        private readonly array $items,
        private array $metadata = [],
    ) {
        $this->pageNumber = 1;
        $items = $this->fullyItems - $this->itemPerPage;

        while ($items > 0) {
            $items -= $this->itemPerPage;
            ++$this->pageNumber;
        }
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getItemPerPage(): int
    {
        return $this->itemPerPage;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getOrderBy(): array
    {
        return $this->orderBy;
    }

    public function getItemOffset(): int
    {
        return $this->itemOffset;
    }

    public function getFullyItems(): int
    {
        return $this->fullyItems;
    }

    public function getPageNumber(): int
    {
        return $this->pageNumber;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function toArray(array $excludedFields = []): array
    {
        $data = [
            'page' => $this->page,
            'itemPerPage' => $this->itemPerPage,
            'filters' => $this->filters,
            'orderBy' => $this->orderBy,
            'itemOffset' => $this->itemOffset,
            'fullyItems' => $this->fullyItems,
            'pageNumber' => $this->pageNumber,
            'items' => $this->items,
            'metadata' => $this->metadata,
        ];

        if (!empty($excludedFields)) {
            foreach ($excludedFields as $excludedField) {
                if (true === isset($data[$excludedField])) {
                    unset($data[$excludedField]);
                }
            }
        }

        return $data;
    }

    public static function fromQuery(Query $query, int $fullyItems = 0, array $items = [], array $metadata = []): self
    {
        return new Page(
            $query->getPage(),
            $query->getItemPerPage(),
            $query->getQueryPart('where'),
            $query->getQueryPart('orderBy'),
            $query->getItemOffset(),
            $fullyItems,
            $items,
            $metadata
        );
    }
}
