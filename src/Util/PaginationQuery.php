<?php
namespace Oka\PaginationBundle\Util;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\AbstractQuery;
use Oka\PaginationBundle\Exception\ObjectManagerNotSupportedException;
use Oka\PaginationBundle\Service\QueryBuilderHandler;
use Oka\PaginationBundle\Twig\OkaPaginationExtension;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class PaginationQuery
{
	const HYDRATE_OBJECT = 0;
	const HYDRATE_ARRAY = 1;
	const TWIG_GLOBAL_VAR_NAME = 'oka_pagination';
	const DEFAULT_TEMPLATE = 'OkaPaginationBundle:Pagination:paginate.html.twig';
	
	/**
	 * @var ObjectManager $objectManager
	 */
	protected $objectManager;
	
	/**
	 * @var QueryBuilderHandler $qbHandler
	 */
	protected $qbHandler;
	
	/**
	 * @var \Twig\Environment $twig
	 */
	protected $twig;
	
	/**
	 * @var string $managerName
	 */
	protected $managerName;
	
	/**
	 * @var array $options
	 */
	protected $options = [
		'twig_extension_enabled' 	=> true,
		'strict_mode' 				=> true
	];
	
	/**
	 * @var array $config
	 */
	protected $config;
	
	/**
	 * @var int $page
	 */
	protected $page;
	
	/**
	 * @var array $_queryParts
	 */
	private $_queryParts = [
		'distinct' 	=> false,
		'select' 	=> [],
		'where' 	=> [],
		'orderBy' 	=> []
    ];
	
	/**
	 * @var string $className
	 */
	protected $className;
	
	/**
	 * @var int $itemPerPage
	 */
	protected $itemPerPage;
	
	/**
	 * @var int $maxPageNumber
	 */
	protected $maxPageNumber;
	
	/**
	 * @var int $fullyItems
	 */
	protected $fullyItems = 0;
	
	/**
	 * @var AbstractQuery $selectQuery
	 */
	protected $selectQuery;
	
	/**
	 * @var AbstractQuery $countQuery
	 */
	protected $countQuery;
	
	/**
	 * @var \Closure $selectItemsCallable
	 */
	protected $selectItemsCallable;
	
	/**
	 * @var \Closure $countItemsCallable
	 */
	protected $countItemsCallable;
	
	/**
	 * @var PaginationResultSet $resultSet
	 */
	protected $resultSet;
	
	/**
	 * @var array $items
	 */
	protected $items;
	
	/**
	 * Constructor.
	 * 
	 * @param ObjectManager $objectManager
	 * @param QueryBuilderHandler $qbHandler
	 * @param \Twig\Environment $twig
	 * @param string $managerName
	 * @param array $options
	 * @param array $config
	 * @param int $page
	 * @param array $criteria
	 * @param array $orderBy
	 * @throws \InvalidArgumentException
	 */
	public function __construct(ObjectManager $objectManager, QueryBuilderHandler $qbHandler, \Twig\Environment $twig = null, $managerName, array $options, array $config, $page, array $criteria = [], array $orderBy = [])
	{
		if (!empty($options)) {
			if ($diff = array_diff(array_keys($options), ['twig_extension_enabled', 'strict_mode'])) {
				throw new \InvalidArgumentException(sprintf('The following options are not supported "%s"', implode(', ', $diff)));
			}
			
			$this->options = $options;
		}
		
		$this->objectManager = $objectManager;
		$this->qbHandler = $qbHandler;
		$this->twig = $twig;
		
		$this->managerName = $managerName;
		$this->config = $config;
		
		$this->addQueryPart('where', $criteria);
		$this->addQueryPart('orderBy', $orderBy);
		$this->loadConfig($config);
		$this->setPage($page);
	}
	
	public function setTwig(\Twig\Environment $twig)
	{
		$this->twig = $twig;
	}
	
	/**
	 * @return array
	 */
	public function getQueryParts()
	{
		return $this->_queryParts;
	}
	
	/**
	 * Either appends to or replaces a single, generic query part.
	 *
	 * The available parts are: 'select', 'distinct', 'where' and 'orderBy'.
	 * 
	 * @param string $queryPartName
	 * @param mixed $queryPart
	 * @param bool $append
	 */
	public function addQueryPart($queryPartName, $queryPart, $append = false)
	{
		$isMultiple = is_array($this->_queryParts[$queryPartName]);
		
		if ($append && $isMultiple) {
			if (is_array($queryPart)) {
				foreach ($queryPart as $key => $part) {
					if (is_numeric($key)) {
						$this->_queryParts[$queryPartName][] = $part;
					} else {
						$this->_queryParts[$queryPartName][$key] = $part;
					}
				}
			} else {
				$this->_queryParts[$queryPartName][] = $queryPart;
			}
		} else {
			$this->_queryParts[$queryPartName] = ($isMultiple && !is_array($queryPart)) ? [$queryPart] : $queryPart;
		}
		
		return $this;
	}
	
	/**
	 * @return int
	 */
	public function getPage()
	{
		return $this->page;
	}
	
	/**
	 * @param int $page
	 * @return \Oka\PaginationBundle\Util\PaginationQuery
	 */
	public function setPage($page)
	{
		$this->page = $this->maxPageNumber < $page ? $this->maxPageNumber : (int) $page;
		return $this;
	}
	
	/**
	 * @return int
	 */
	public function getItemPerPage()
	{
		return $this->itemPerPage;
	}
	
	/**
	 * @param int $itemPerPage
	 * @return \Oka\PaginationBundle\Util\PaginationQuery
	 */
	public function setItemPerPage($itemPerPage)
	{
		$this->itemPerPage = $itemPerPage;
		return $this;
	}
	
	/**
	 * @return int
	 */
	public function getMaxPageNumber()
	{
		return $this->maxPageNumber;
	}
	
	/**
	 * @param AbstractQuery $query
	 * @return \Oka\PaginationBundle\Util\PaginationQuery
	 */
	public function setCountQuery(AbstractQuery $query)
	{
		$this->countQuery = $query;
		return $this;
	}
	
	/**
	 * @param AbstractQuery $query
	 * @return \Oka\PaginationBundle\Util\PaginationQuery
	 */
	public function setSelectQuery(AbstractQuery $query)
	{
		$this->selectQuery = $query;
		return $this;
	}
	
	/**
	 * @param \Closure $closure
	 * @return \Oka\PaginationBundle\Util\PaginationQuery
	 */
	public function setCountItemsCallable(\Closure $closure)
	{
		$this->countItemsCallable = $closure;
		return $this;
	}
	
	/**
	 * @param \Closure $closure
	 * @return \Oka\PaginationBundle\Util\PaginationQuery
	 */
	public function setSelectItemsCallable(\Closure $closure)
	{
		$this->selectItemsCallable = $closure;
		return $this;
	}
	
	/**
	 * Fetch page
	 * 
	 * @param integer $hydrationMode
	 * @throws \LogicException
	 * @throws \UnexpectedValueException
	 * @return \Oka\PaginationBundle\Util\PaginationResultSet
	 */
	public function fetch($hydrationMode = self::HYDRATE_OBJECT)
	{
		if (null === $this->resultSet) {
			$items = [];
			$objectRepository = $this->objectManager->getRepository($this->className);
			
			if ($this->countItemsCallable instanceof \Closure) {
				$fn = $this->countItemsCallable;
				$this->fullyItems = $fn($objectRepository, $this->_queryParts['where']);
					
				if (!is_integer($this->fullyItems)) {
					throw new \UnexpectedValueException('The closure "countItemsCallable" returned an unexcepted value.');
				}
			} elseif ($this->countQuery instanceof \Doctrine\ORM\Query/* || $this->countQuery instanceof \Doctrine\ODM\MongoDB\Query\Query*/) {
				$this->fullyItems = $this->countQuery->getSingleScalarResult();
			} else {
				$this->internalCountQuery = $this->createCountQuery($this->_queryParts['select'], $this->_queryParts['where'], $this->_queryParts['distinct']);
				$this->fullyItems = (int) ($this->internalCountQuery instanceof \Doctrine\ORM\Query ?
						$this->internalCountQuery->getSingleScalarResult() : $this->internalCountQuery->execute());
			}
			
			if ($this->fullyItems > 0) {
				if ($this->selectItemsCallable instanceof \Closure) {
					$fn = $this->selectItemsCallable;
					$items = $fn($objectRepository, $this->_queryParts['where'], $this->_queryParts['orderBy'], $this->itemPerPage, $this->getItemOffset());
			
					if (!is_array($items)) {
						throw new \UnexpectedValueException('The closure "selectItemsCallable" returned an unexcepted value.');
					}
				} elseif ($this->selectQuery instanceof \Doctrine\ORM\Query) {
					$items = $this->selectQuery->setFirstResult($this->getItemOffset())
												->setMaxResults($this->itemPerPage)
												->getResult();
				} else {
					$this->internalSelectQuery = $this->createSelectQuery($this->_queryParts['select'], $this->_queryParts['where'], $this->_queryParts['orderBy'], $this->_queryParts['distinct']);
					$items = $this->internalSelectQuery instanceof \Doctrine\ORM\Query ?
					$this->internalSelectQuery->getResult() : $this->internalSelectQuery->execute()->toArray(false);
				}
			}
			
			// Set pagination result set
			$this->setResultSet($items);
			
			if (isset($this->options['twig_extension_enabled']) && true === $this->options['twig_extension_enabled']) {
				$globals = $this->twig->getGlobals();
					
				if (isset($globals[OkaPaginationExtension::TWIG_GLOBAL_VAR_NAME])) {
					$globals[OkaPaginationExtension::TWIG_GLOBAL_VAR_NAME][$this->managerName] = $this->resultSet->toArray(['items']);
					$this->twig->addGlobal(OkaPaginationExtension::TWIG_GLOBAL_VAR_NAME, $globals[OkaPaginationExtension::TWIG_GLOBAL_VAR_NAME]);
				}
			}
		}
		
		return $hydrationMode == self::HYDRATE_ARRAY ? $this->resultSet->toArray() : $this->resultSet;
	}
	
	/**
	 * Load config
	 * 
	 * @param array $config
	 * @throws \InvalidArgumentException
	 * @return array
	 */
	protected function loadConfig(array $config)
	{
		$this->className = $config['class'];
		$this->itemPerPage = (int) $config['item_per_page'];
		$this->maxPageNumber = (int) $config['max_page_number'];		
	}
	
	/**
	 * Create internal count items query
	 * 
	 * @param array $fields
	 * @param array $criteria
	 * @param bool $distinct
	 * @return \Doctrine\ORM\AbstractQuery|\Doctrine\ODM\MongoDB\Query\Query
	 */
	protected function createCountQuery(array $fields = [], array $criteria = [], $distinct = true)
	{
		if ($this->objectManager instanceof \Doctrine\ORM\EntityManager) {
			/** @var \Doctrine\ORM\QueryBuilder $builder */
			$builder = $this->objectManager->createQueryBuilder();
			
			if (empty($fields)) {
				/** @var \Doctrine\Common\Persistence\Mapping\ClassMetadata $classMetadata */
				$classMetadata = $this->objectManager->getClassMetadata($this->className);
				$identifier = $classMetadata->getIdentifierFieldNames()[0];
			} else {
				$identifier = $fields[0]; // Replace with $identifier = implode(', p.', $fields);
			}
			
			$builder->select($distinct ? $builder->expr()->countDistinct('p.' . $identifier) : $builder->expr()->count('p.' . $identifier))
					->from($this->className, 'p');
		} elseif ($this->objectManager instanceof \Doctrine\ODM\MongoDB\DocumentManager) {
			/** @var \Doctrine\ODM\MongoDB\Query\Builder $builder */
			$builder = $this->objectManager->createQueryBuilder($this->className);
			
			if (false === empty($fields) && true === $distinct) {
				$builder->distinct($fields[0]);
			}
			
			$builder->count();
		} else {
			throw new ObjectManagerNotSupportedException(sprintf('Doctrine object manager class "%s" is not supported.', get_class($this->objectManager)));
		}
		
		$this->qbHandler->applyExprFromArray($builder, 'p', $criteria);
		
		return $builder->getQuery();
	}
	
	/**
	 * Create internal select items query
	 * 
	 * @param array $fields
	 * @param array $criteria
	 * @param array $orderBy
	 * @param bool $distinct
	 * @return \Doctrine\ORM\AbstractQuery|\Doctrine\ODM\MongoDB\Query\Query
	 */
	protected function createSelectQuery(array $fields = [], array $criteria = [], array $orderBy = [], $distinct = true)
	{
		if ($this->objectManager instanceof \Doctrine\ORM\EntityManager) {
			/** @var \Doctrine\ORM\QueryBuilder $builder */
			$builder = $this->objectManager->createQueryBuilder();
			$builder->select(empty($fields) ? 'p' : 'p.' . implode(', p.', $fields));
			
			if (true === $distinct) {
				$builder->distinct();
			}
			
			$builder->from($this->className, 'p')
					->setFirstResult($this->getItemOffset())
					->setMaxResults($this->itemPerPage);
			
			foreach ($orderBy as $key => $value) {
				$builder->orderBy(sprintf('p.%s', $key), $value);
			}
		} elseif ($this->objectManager instanceof \Doctrine\ODM\MongoDB\DocumentManager) {
			/** @var \Doctrine\ODM\MongoDB\Query\Builder $builder */
			$builder = $this->objectManager->createQueryBuilder($this->className);
			
			if (false === empty($fields)) {
				$builder->select($fields);
			}
			
			$builder->skip($this->getItemOffset())
					->limit($this->itemPerPage);
			
			foreach ($orderBy as $key => $value) {
				$builder->sort($key, $value);
			}
		} else {
			throw new ObjectManagerNotSupportedException(sprintf('Doctrine object manager class "%s" is not supported.', get_class($this->objectManager)));
		}
		
		$this->qbHandler->applyExprFromArray($builder, 'p', $criteria);
		
		return $builder->getQuery();
	}
	
	/**
	 * @return int
	 */
	protected function getItemOffset()
	{
		return $this->page < 2 ? 
			0 : $this->itemPerPage * ($this->maxPageNumber < $this->page ? $this->maxPageNumber - 1 : $this->page - 1);
	}
	
	/**
	 * Get page number
	 * 
	 * @return int
	 */
	protected function countPage()
	{
		$pageNumber = 0;
		$items = $this->fullyItems - $this->itemPerPage;
		
		while ($items > 0) {
			++$pageNumber;
			$items -= $this->itemPerPage;
		}
		++$pageNumber;
		
		return $pageNumber;
	}
	
	protected function setResultSet(array $items) {
		$this->resultSet = new PaginationResultSet(
				$this->page, 
				$this->itemPerPage, 
				$this->_queryParts['where'], 
				$this->_queryParts['orderBy'], 
				$this->getItemOffset(), 
				$this->fullyItems, 
				$this->countPage(), 
				$items
		);
		return $this;
	}
}
