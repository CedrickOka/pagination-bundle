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
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
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
	 * @var \Doctrine\ORM\QueryBuilder|\Doctrine\ODM\MongoDB\Query\Builder
	 */
	private $dbalQueryBuilder;
	
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
			'orderBy' => []
		];
		$this->dqlAlias = 'p';
		
		// Query part where
		foreach ($criteria as $key => $value) {
			$this->addQueryPart('where', [$key => $value], true);
		}
		
		// Query part orderBy
		foreach ($orderBy as $key => $value) {
			$this->addQueryPart('orderBy', [$key => $value], true);
		}
	}
	
	public function getClassName() :string
	{
		return $this->className;
	}
	
	public function getItemPerPage() :int
	{
		return $this->itemPerPage;
	}
	
	public function getMaxPageNumber() :int
	{
		return $this->maxPageNumber;
	}
	
	public function getFilterBag() :FilterBag
	{
		return $this->filters;
	}
	
	public function getPage() :int
	{
		return $this->page;
	}
	
	public function getQueryParts() :array
	{
		return $this->queryParts;
	}
	
	/**
	 * Either appends to or replaces a single, generic query part.
	 * 
	 * The available parts are: 'select', 'distinct', 'where' and 'orderBy'.
	 * 
	 * @param string $queryPartName
	 * @param mixed $queryPart
	 * @param bool $append
	 * @return \Oka\PaginationBundle\Pagination\Query
	 */
	public function addQueryPart(string $queryPartName, $queryPart, bool $append = false) :self
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
	
	public function getDqlAlias() :string
	{
		return $this->dqlAlias;
	}
	
	public function setDqlAlias(string $dqlAlias) :self
	{
		$this->dqlAlias = $dqlAlias;
		return $this;
	}
	
	public function setDBALQueryBuilder(object $dbalQueryBuilder) :self
	{
		if (!$dbalQueryBuilder instanceof QueryBuilder && !$dbalQueryBuilder instanceof Builder) {
			throw new ObjectManagerNotSupportedException(sprintf('Doctrine DBAL query builder class "%s" is not supported.', get_class($dbalQueryBuilder)));
		}
		
		$this->dbalQueryBuilder = $dbalQueryBuilder;
		return $this;
	}
	
	public function execute() :Page
	{
		$items = [];
		$boundCounter = 1;
		$itemOffset = $this->getItemOffset();
		$builder = $this->dbalQueryBuilder ?? $this->createDBALQueryBuilder();
		/** @var \Doctrine\Persistence\Mapping\ClassMetadata $classMetadata */
		$classMetadata = $this->objectManager->getClassMetadata($this->className);
		
		foreach ($this->queryParts['where'] as $key => $value) {
			if (false === $this->filters->has($key)) {
				throw new FilterNotAvailableException(sprintf('Pagination filter "%s" is not available for criteria.', $key));
			}
			
			/** @var \Oka\PaginationBundle\Pagination\Filter $filter */
			$filter = $this->filters->get($key);
			$propertyName = $filter->getPropertyName();
			$propertyType = null;
			
			if ($builder instanceof QueryBuilder) {
				$propertyType = PersisterHelper::getTypeOfField($propertyName, $classMetadata, $this->objectManager)[0] ?? null;
				$propertyName = sprintf('%s.%s', $this->dqlAlias, $propertyName);
			}
			
			$this->filterHandler->evaluate($builder, $propertyName, $value, $filter->getCastType(), $propertyType, $boundCounter);
		}
		
		if ($builder instanceof QueryBuilder) {
			$identifier = sprintf('%s.%s', $this->dqlAlias, $classMetadata->getIdentifierFieldNames()[0]);
			$fullyItems = (int) $builder->select(true === $this->queryParts['distinct'] ?
											$builder->expr()->countDistinct($identifier) : 
											$builder->expr()->count($identifier)
										)
										->from($this->className, $this->dqlAlias)
										->getQuery()
										->getSingleScalarResult();
			
			if ($fullyItems > 0) {
				foreach ($this->queryParts['orderBy'] as $sort => $order) {
					$builder->addOrderBy(sprintf('%s.%s', $this->dqlAlias, $this->getSortName($sort)), $order);
				}
				
				$items = $builder->select($this->dqlAlias)
								->setFirstResult($itemOffset)
								->setMaxResults($this->itemPerPage)
								->getQuery()
								->getResult();
			}
		} elseif ($builder instanceof Builder) {
			$fullyItems = (int) $builder->count()
										->getQuery()
										->execute();
			
			if ($fullyItems > 0) {
				foreach ($this->queryParts['orderBy'] as $sort => $order) {
					$builder->sort($this->getSortName($sort), $order);
				}
				
				$items = $builder->find()
								->skip($itemOffset)
								->limit($this->itemPerPage)
								->getQuery()
								->execute()
								->toArray(false);
			}
		}
		
		return new Page(
			$this->page, 
			$this->itemPerPage, 
			$this->queryParts['where'], 
			$this->queryParts['orderBy'], 
			$itemOffset, 
			$fullyItems, 
			$this->countPage($fullyItems), 
			$items
		);
	}
	
	/**
	 * @deprecated Use instead Query::execute() method.
	 */
	public function fetch() :Page
	{
		return $this->execute();
	}
	
	/**
	 * @return \Doctrine\ODM\MongoDB\Query\Builder|\Doctrine\ORM\QueryBuilder
	 */
	protected function createDBALQueryBuilder() :object
	{
		switch (true) {
			case $this->objectManager instanceof \Doctrine\ORM\EntityManager:
				/** @var \Doctrine\ORM\QueryBuilder $builder */
				return $this->objectManager->createQueryBuilder();
				
			case $this->objectManager instanceof \Doctrine\ODM\MongoDB\DocumentManager:
				/** @var \Doctrine\ODM\MongoDB\Query\Builder $builder */
				return $this->objectManager->createQueryBuilder($this->className);
			
			default:
				throw new ObjectManagerNotSupportedException(sprintf('Doctrine object manager class "%s" is not supported.', get_class($this->objectManager)));
		}
	}
	
	protected function getItemOffset() :int
	{
		return $this->page < 2 ? 
			0 : 
			$this->itemPerPage * (
				$this->maxPageNumber < $this->page ? 
				$this->maxPageNumber - 1 : 
				$this->page - 1
			);
	}
	
	protected function countPage(int $fullyItems) :int
	{
		$pageNumber = 1;
		$items = $fullyItems - $this->itemPerPage;
		
		while ($items > 0) {
			$items -= $this->itemPerPage;
			++$pageNumber;
		}
		
		return $pageNumber;
	}
	
	protected function getSortName(string $sort) :string
	{
		if (false === $this->filters->has($sort)) {
			throw new SortAttributeNotAvailableException($sort, sprintf('Invalid request sort attributes "%s" not available.', $sort));
		}
		
		/** @var \Oka\PaginationBundle\Pagination\Filter $filter */
		$filter = $this->filters->get($sort);
		
		return $filter->getPropertyName();
	}
}
