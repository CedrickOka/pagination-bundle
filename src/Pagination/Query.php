<?php

namespace Oka\PaginationBundle\Pagination;

use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Utility\PersisterHelper;
use Doctrine\Persistence\ObjectManager;
use Oka\PaginationBundle\Exception\FilterNotAvailableException;
use Oka\PaginationBundle\Exception\ObjectManagerNotSupportedException;
use Oka\PaginationBundle\Exception\SortAttributeNotAvailableException;
use Oka\PaginationBundle\Pagination\FilterExpression\FilterExpressionHandler;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class Query
{
    private $objectManager;
    private $filterHandler;
    private $className;
    private $itemPerPage;
    private $maxPageNumber;
    private $filters;
    private $page;

    /**
     * @var array
     */
    private $queryParts;

    /**
     * @var string
     */
    private $dqlAlias;

    /**
     * @var QueryBuilder|Builder
     */
    private $dbalQueryBuilder;

    /**
     * @var int
     */
    private $boundCounter;

    public function __construct(ObjectManager $objectManager, FilterExpressionHandler $filterHandler, string $className, int $itemPerPage, int $maxPageNumber, FilterBag $filters, int $page, array $criteria = [], array $orderBy = [])
    {
        if (1 > $itemPerPage) {
            throw new \LogicException(sprintf('The number of items per page must be greater than 0, "%s" given.', $itemPerPage));
        }

        $this->objectManager = $objectManager;
        $this->filterHandler = $filterHandler;
        $this->className = $className;
        $this->itemPerPage = $itemPerPage;
        $this->maxPageNumber = $maxPageNumber;
        $this->filters = $filters;
        $this->page = $page;
        $this->queryParts = [
            'distinct' => false,
            'select' => [],
            'where' => [],
            'orderBy' => [],
        ];
        $this->dqlAlias = 'p';
        $this->boundCounter = 1;

        // Query part where
        foreach ($criteria as $key => $value) {
            $this->addQueryPart('where', [$key => $value], true);
        }

        // Query part orderBy
        foreach ($orderBy as $key => $value) {
            $this->addQueryPart('orderBy', [$key => $value], true);
        }

        $this->dbalQueryBuilder = $this->createDBALQueryBuilder();
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getItemPerPage(): int
    {
        return $this->itemPerPage;
    }

    public function getMaxPageNumber(): int
    {
        return $this->maxPageNumber;
    }

    public function getFilterBag(): FilterBag
    {
        return $this->filters;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getItemOffset(): int
    {
        return $this->page < 2 ? 0 : $this->itemPerPage * ($this->maxPageNumber < $this->page ? $this->maxPageNumber - 1 : $this->page - 1);
    }

    public function getDqlAlias(): string
    {
        return $this->dqlAlias;
    }

    public function setDqlAlias(string $dqlAlias): self
    {
        $this->dqlAlias = $dqlAlias;

        return $this;
    }

    public function getDBALQueryBuilder(): object
    {
        return $this->dbalQueryBuilder;
    }

    public function setDBALQueryBuilder(object $dbalQueryBuilder): self
    {
        if (!$dbalQueryBuilder instanceof QueryBuilder && !$dbalQueryBuilder instanceof Builder) {
            throw new ObjectManagerNotSupportedException(sprintf('Doctrine DBAL query builder class "%s" is not supported.', get_class($dbalQueryBuilder)));
        }

        $this->dbalQueryBuilder = $dbalQueryBuilder;

        return $this;
    }

    public function getBoundCounter(): int
    {
        return $this->boundCounter;
    }

    public function setBoundCounter(int $boundCounter): self
    {
        $this->boundCounter = $boundCounter;

        return $this;
    }

    public function useBoundCounter(): int
    {
        return $this->boundCounter++;
    }

    public function getQueryParts(): array
    {
        return $this->queryParts;
    }

    public function getQueryPart(string $queryPartName)
    {
        return $this->queryParts[$queryPartName] ?? null;
    }

    /**
     * Either appends to or replaces a single, generic query part.
     *
     * The available parts are: 'select', 'distinct', 'where' and 'orderBy'.
     */
    public function addQueryPart(string $queryPartName, $queryPart, bool $append = false): self
    {
        if (true === $append && 'distinct' === $queryPartName) {
            throw new \InvalidArgumentException('Using \$append = true does not have an effect with "distinct" parts');
        }

        $isMultiple = is_array($this->queryParts[$queryPartName]);

        if (true === $append && true === $isMultiple) {
            if (true === is_array($queryPart)) {
                foreach ($queryPart as $key => $part) {
                    if (true === is_numeric($key)) {
                        $this->queryParts[$queryPartName][] = $part;
                    } else {
                        $this->queryParts[$queryPartName][$key] = $part;
                    }
                }
            } else {
                $this->queryParts[$queryPartName][] = $queryPart;
            }
        } else {
            $this->queryParts[$queryPartName] = (true === $isMultiple && false === is_array($queryPart)) ? [$queryPart] : $queryPart;
        }

        return $this;
    }

    public function getSortPropertyName(string $sort): string
    {
        if (false === $this->filters->has($sort)) {
            throw new SortAttributeNotAvailableException([$sort], sprintf('Invalid request sort attributes "%s" not available.', $sort));
        }

        /** @var Filter $filter */
        $filter = $this->filters->get($sort);

        return $filter->getPropertyName();
    }

    public function execute(): Page
    {
        $items = [];
        $itemOffset = $this->getItemOffset();
        $boundCounter = $this->getBoundCounter();
        /** @var \Doctrine\Persistence\Mapping\ClassMetadata $classMetadata */
        $classMetadata = $this->objectManager->getClassMetadata($this->className);

        foreach ($this->queryParts['where'] as $key => $value) {
            if (false === $this->filters->has($key)) {
                throw new FilterNotAvailableException(sprintf('Pagination filter "%s" is not available for criteria.', $key));
            }

            /** @var Filter $filter */
            $filter = $this->filters->get($key);
            $propertyName = $filter->getPropertyName();
            $propertyType = null;

            if ($this->dbalQueryBuilder instanceof QueryBuilder) {
                $propertyType = PersisterHelper::getTypeOfField($propertyName, $classMetadata, $this->objectManager)[0] ?? null;
                $propertyName = sprintf('%s.%s', $this->dqlAlias, $propertyName);
            }

            $this->filterHandler->evaluate($this->dbalQueryBuilder, $propertyName, $value, $filter->getCastType(), $propertyType, $boundCounter);
        }

        if ($this->dbalQueryBuilder instanceof QueryBuilder) {
            $identifier = sprintf('%s.%s', $this->dqlAlias, $classMetadata->getIdentifierFieldNames()[0]);
            $builder = clone $this->dbalQueryBuilder;
            $fullyItems = (int) $builder->select(
                true === $this->queryParts['distinct'] ?
                                            $builder->expr()->countDistinct($identifier) :
                                            $builder->expr()->count($identifier)
            )
                                        ->getQuery()
                                        ->getSingleScalarResult();

            if ($fullyItems > 0) {
                foreach ($this->queryParts['orderBy'] as $sort => $order) {
                    $this->dbalQueryBuilder->addOrderBy(sprintf('%s.%s', $this->dqlAlias, $this->getSortPropertyName($sort)), $order);
                }

                if (true === $this->queryParts['distinct']) {
                    $this->dbalQueryBuilder->distinct();
                }

                $items = $this->dbalQueryBuilder->addSelect($this->dqlAlias)
                                                ->setFirstResult($itemOffset)
                                                ->setMaxResults($this->itemPerPage)
                                                ->getQuery()
                                                ->getResult();
            }
        } elseif ($this->dbalQueryBuilder instanceof Builder) {
            $builder = clone $this->dbalQueryBuilder;
            $fullyItems = (int) $builder->count()
                                        ->getQuery()
                                        ->execute();

            if ($fullyItems > 0) {
                foreach ($this->queryParts['orderBy'] as $sort => $order) {
                    $this->dbalQueryBuilder->sort($this->getSortPropertyName($sort), $order);
                }

                $items = $this->dbalQueryBuilder->find()
                                                ->skip($itemOffset)
                                                ->limit($this->itemPerPage)
                                                ->getQuery()
                                                ->execute()
                                                ->toArray(false);
            }
        }

        return Page::fromQuery($this, $fullyItems, $items);
    }

    /**
     * @return Builder|QueryBuilder
     */
    protected function createDBALQueryBuilder(): object
    {
        switch (true) {
            case $this->objectManager instanceof \Doctrine\ORM\EntityManager:
                /* @var \Doctrine\ORM\QueryBuilder $builder */
                return $this->objectManager->createQueryBuilder()
                            ->from($this->className, $this->dqlAlias);

            case $this->objectManager instanceof \Doctrine\ODM\MongoDB\DocumentManager:
                /* @var \Doctrine\ODM\MongoDB\Query\Builder $builder */
                return $this->objectManager->createQueryBuilder($this->className);

            default:
                throw new ObjectManagerNotSupportedException(sprintf('Doctrine object manager class "%s" is not supported.', get_class($this->objectManager)));
        }
    }
}
