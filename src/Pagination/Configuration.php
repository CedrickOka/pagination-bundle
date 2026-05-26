<?php

declare(strict_types=1);

namespace Oka\PaginationBundle\Pagination;

use Symfony\Component\Routing\Route;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
final class Configuration
{
    public function __construct(
        private readonly string $dbDriver,
        private readonly int $itemPerPage,
        private int $maxPageNumber,
        private readonly array $sort,
        private readonly array $queryMappings,
        private readonly FilterBag $filters,
        private readonly ?string $objectManagerName = null,
        private readonly ?string $className = null,
        private readonly ?Route $route = null,
        private readonly array $twig = [],
    ) {
        if ($diff = array_diff(array_keys($twig), ['enabled', 'template'])) {
            throw new \InvalidArgumentException(sprintf('The following options given "%s" for the arguments "$twig" are not valids.', implode(',', $diff)));
        }
    }

    public function getDBDriver(): string
    {
        return $this->dbDriver;
    }

    public function getItemPerPage(): int
    {
        return $this->itemPerPage;
    }

    public function getMaxPageNumber(): int
    {
        return $this->maxPageNumber;
    }

    public function setMaxPageNumber(int $maxPageNumber): self
    {
        $this->maxPageNumber = $maxPageNumber;

        return $this;
    }

    public function getSort(): array
    {
        return $this->sort;
    }

    public function getQueryMappings(): array
    {
        return $this->queryMappings;
    }

    public function getFilters(): FilterBag
    {
        return $this->filters;
    }

    public function getObjectManagerName(): ?string
    {
        return $this->objectManagerName;
    }

    public function getClassName(): ?string
    {
        return $this->className;
    }

    public function getRoute(): ?Route
    {
        return $this->route;
    }

    public function getTwig(): array
    {
        return $this->twig;
    }
}
