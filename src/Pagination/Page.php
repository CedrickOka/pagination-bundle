<?php

namespace Oka\PaginationBundle\Pagination;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class Page
{
    private $page;
    private $itemPerPage;
    private $filters;
    private $orderBy;
    private $itemOffset;
    private $fullyItems;
    private $items;
    private $pageNumber;

    public function __construct(int $page, int $itemPerPage, array $filters, array $orderBy, int $itemOffset, int $fullyItems, int $pageNumber, array $items)
    {
        $this->page = $page;
        $this->itemPerPage = $itemPerPage;
        $this->filters = $filters;
        $this->orderBy = $orderBy;
        $this->itemOffset = $itemOffset;
        $this->fullyItems = $fullyItems;
        $this->items = $items;

        if (0 < $pageNumber) {
            $this->pageNumber = $pageNumber;
        } else {
            $this->pageNumber = 1;
            $items = $this->fullyItems - $this->itemPerPage;

            while ($items > 0) {
                $items -= $this->itemPerPage;
                ++$this->pageNumber;
            }
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

    public function getItems(): array
    {
        return $this->items;
    }

    public function getPageNumber(): int
    {
        return $this->pageNumber;
    }

    public function toArray(array $exludedFields = []): array
    {
        $data = [
            'page' 			=> $this->page,
            'itemPerPage' 	=> $this->itemPerPage,
            'filters' 		=> $this->filters,
            'orderBy' 		=> $this->orderBy,
            'itemOffset' 	=> $this->itemOffset,
            'fullyItems' 	=> $this->fullyItems,
            'pageNumber' 	=> $this->pageNumber,
            'items' 		=> $this->items
        ];

        if (false === empty($exludedFields)) {
            foreach ($exludedFields as $exludedField) {
                if (true === isset($data[$exludedField])) {
                    unset($data[$exludedField]);
                }
            }
        }

        return $data;
    }
}
