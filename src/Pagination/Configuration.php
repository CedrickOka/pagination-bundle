<?php

namespace Oka\PaginationBundle\Pagination;

use Symfony\Component\Routing\Route;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
final class Configuration
{
    private $dbDriver;
    private $itemPerPage;
    private $maxPageNumber;
    private $sort;
    private $queryMappings;
    private $filters;
    private $objectManagerName;
    private $className;
    private $route;
    private $twig;

    public function __construct(string $dbDriver, int $itemPerPage, int $maxPageNumber, array $sort, array $queryMappings, FilterBag $filters, string $objectManagerName = null, string $className = null, Route $route = null, array $twig = [])
    {
        if ($diff = array_diff(array_keys($twig), ['enabled', 'template'])) {
            throw new \InvalidArgumentException(sprintf('The following options given "%s" for the arguments "$twig" are not valids.', implode(',', $diff)));
        }

        $this->dbDriver = $dbDriver;
        $this->itemPerPage = $itemPerPage;
        $this->maxPageNumber = $maxPageNumber;
        $this->sort = $sort;
        $this->filters = $filters;
        $this->objectManagerName = $objectManagerName;
        $this->className = $className;
        $this->queryMappings = $queryMappings;
        $this->route = $route;
        $this->twig = $twig;
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
