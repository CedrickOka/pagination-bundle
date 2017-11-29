<?php
namespace Oka\PaginationBundle\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Query;
use Oka\PaginationBundle\DependencyInjection\OkaPaginationExtension as BundleExtension;
use Oka\PaginationBundle\Exception\ObjectManagerNotSupportedException;
use Oka\PaginationBundle\Exception\SortAttributeNotAvailableException;
use Oka\PaginationBundle\Twig\OkaPaginationExtension as TwigExtension;
use Oka\PaginationBundle\Util\PaginationResultSet;
use Oka\PaginationBundle\Util\RequestParser;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class PaginationManager
{
	const HYDRATE_OBJECT = 0;
	const HYDRATE_ARRAY = 1;
	
	/**
	 * @var ContainerInterface $container
	 */
	protected $container;
	
	/**
	 * @var ObjectManager $objectManager
	 */
	protected $objectManager;
	
	/**
	 * @var PaginationManagerBag $paginationManagerBag
	 */
	protected $paginationManagerBag;
	
	/**
	 * @var QueryBuilderManipulator $manipulator
	 */
	protected $manipulator;
	
	/**
	 * @var integer $itemPerPage
	 */
	protected $itemPerPage;
	
	/**
	 * @var integer $maxPageNumber
	 */
	protected $maxPageNumber;
	
	/**
	 * @var string $className
	 */
	protected $className;
	
	/**
	 * @var integer $page
	 */
	protected $page;
	
	/**
	 * @var Query $selectQuery
	 */
	protected $selectQuery;

	/**
	 * @var Query $countQuery
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
	 * @var array $criteria
	 */
	protected $criteria;
	
	/**
	 * @var array $orderBy
	 */
	protected $orderBy;
	
	/**
	 * @var integer $fullyItems
	 */
	protected $fullyItems;
	
	/**
	 * @var integer $pageNumber
	 */
	protected $pageNumber;
	
	/**
	 * @var string $currentManagerName
	 */
	private $currentManagerName;
	
	/**
	 * @var array $defaultManagerConfig
	 */
	private $defaultManagerConfig;

	/**
	 * @var Query $internalSelectQuery
	 */
	private $internalSelectQuery;
	
	/**
	 * @var Query $internalCountQuery
	 */
	private $internalCountQuery;
	
	/**
	 * @var boolean $prepared
	 */
	private $prepared = false;
	
	/**
	 * @var array $paginationStore
	 */
	private $paginationStore = [];
	
	/**
	 * @param ContainerInterface $container
	 * @param PaginationManagerBag $paginationManagerBag
	 * @param QueryBuilderManipulator $manipulator
	 * @param integer $itemPerPage
	 * @param integer $maxPageNumber
	 * @param string $template
	 * @param array $request
	 * @param array $sort
	 */
	public function __construct(ContainerInterface $container, PaginationManagerBag $paginationManagerBag, QueryBuilderManipulator $manipulator, $itemPerPage, $maxPageNumber, $template = null, array $request)
	{
		$this->container = $container;
		$this->paginationManagerBag = $paginationManagerBag;
		$this->manipulator = $manipulator;
		$this->page = 1;
		$this->criteria = [];
		$this->orderBy = [];
		$this->fullyItems = 0;
		$this->pageNumber = null;
		$this->defaultManagerConfig = [
				'item_per_page' 	=> $itemPerPage,
				'max_page_number' 	=> $maxPageNumber,
				'template' 			=> $template,
				'request' 			=> $request
		];
	}
	
	/**
	 * @return number
	 */
	public function getPage()
	{
		return $this->page;
	}
	
	/**
	 * @param integer $itemPerPage
	 * @return \Oka\Pagination\Service\Pagination
	 */
	public function setItemPerPage($itemPerPage)
	{
		$this->itemPerPage = $itemPerPage;
		return $this;
	}
	
	/**
	 * @return number
	 */
	public function getItemPerPage()
	{
		return $this->itemPerPage;
	}
	
	/**
	 * @return integer
	 */
	public function getMaxPageNumber()
	{
		return $this->maxPageNumber;
	}
	
	/**
	 * @param integer $maxPageNumber
	 * @return \Oka\Pagination\Service\Pagination
	 */
	public function setMaxPageNumber($maxPageNumber) 
	{
		$this->maxPageNumber = $maxPageNumber;
		return $this;
	}
	
	/**
	 * @param Query $query
	 * @return \Oka\PaginationBundle\Service\PaginationManager
	 */
	public function setCountQuery(Query $query)
	{
		$this->countQuery = $query;
		return $this;
	}

	/**
	 * @param Query $query
	 * @return \Oka\PaginationBundle\Service\PaginationManager
	 */
	public function setSelectQuery(Query $query)
	{
		$this->selectQuery = $query;
		return $this;
	}
	
	/**
	 * @param \Closure $closure
	 * @return \Oka\PaginationBundle\Service\PaginationManager
	 */
	public function setSelectItemsCallable(\Closure $closure)
	{
		$this->selectItemsCallable = $closure;
		return $this;
	}
	
	/**
	 * @param \Closure $closure
	 * @return \Oka\PaginationBundle\Service\PaginationManager
	 */
	public function setCountItemsCallable(\Closure $closure)
	{
		$this->countItemsCallable = $closure;
		return $this;
	}
	
	/**
	 * Get pagination manager config
	 * 
	 * @param string $managerName
	 * @throws \InvalidArgumentException
	 * @return array
	 */
	public function getManagerConfig($managerName)
	{
		if ($this->paginationManagerBag->has($managerName)) {
			$managerConfig = $this->paginationManagerBag->get($managerName, []);
			
			if (isset($managerConfig['sort'])) {
				if ($managerConfig['sort']['delimiter'] !== null && $managerConfig['request']['sort']['delimiter'] === ',') {
					$managerConfig['request']['sort']['delimiter'] = $managerConfig['sort']['delimiter'];
					@trigger_error(sprintf('The configuration value `oka_pagination.pagination_managers.%1$s.sort.delimiter` is deprecated since 1.3.0 and will be removed in 1.4.0. Use `oka_pagination.pagination_managers.%1$s.request.sort.delimiter` instead', $managerName), E_USER_DEPRECATED);
				}
				if (!empty($managerConfig['sort']['attributes_availables']) && empty($managerConfig['request']['sort']['attributes_availables'])) {
					$managerConfig['request']['sort']['attributes_availables'] = $managerConfig['sort']['attributes_availables'];
					@trigger_error(sprintf('The configuration value `oka_pagination.pagination_managers.%1$s.sort.attributes_availables` is deprecated since 1.3.0 and will be removed in 1.4.0. Use `oka_pagination.pagination_managers.%1$s.request.sort.attributes_availables` instead', $managerName), E_USER_DEPRECATED);
				}
				
				unset($managerConfig['sort']);
				$this->paginationManagerBag->set($managerName, $managerConfig);
			}
		} elseif (class_exists($managerName)) {
			$managerConfig = $this->defaultManagerConfig;
			$managerConfig['class'] = $managerName;
			
		} else {
			throw new \InvalidArgumentException(sprintf('The "%s" configuration key is not attached to a pagination manager.', $managerName));
		}
		
		return $managerConfig;
	}
	
	/**
	 * Paginate query
	 * 
	 * @param string $managerName
	 * @param Request $request
	 * @param array $criteria
	 * @param array $orderBy
 	 * @param boolean $strictMode Throw exception if value has true and parse request occur an error
	 * @param integer $hydrationMode
	 * @throws SortAttributeNotAvailableException
	 * @throws \UnexpectedValueException
	 * @return PaginationResultSet
	 */
	public function paginate($managerName, Request $request, array $criteria = [], array $orderBy = [], $strictMode = true, $hydrationMode = self::HYDRATE_OBJECT)
	{
		return $this->prepare($managerName, $request, $criteria, $orderBy, $strictMode)
					->fetch($hydrationMode);
	}
	
	/**
	 * Prepare pagination query
	 * 
	 * @param string $managerName
	 * @param Request $request
	 * @param array $criteria
	 * @param array $orderBy
	 * @throws SortAttributeNotAvailableException
	 * @return \Oka\PaginationBundle\Service\PaginationManager
	 */
	public function prepare($managerName, Request $request, array $criteria = [], array $orderBy = [], $strictMode = true)
	{
		// Load entity pagination manager config
		$managerConfig = $this->loadManagerConfig($managerName);
		$queryMapConfig = $managerConfig['request']['query_map'];
		$sortConfig = $managerConfig['request']['sort'];
		
		// Extract pagination data in request
		$this->extractPageInRequest($request, $queryMapConfig['page']);
		$this->extractItemPerPageInRequest($request, $queryMapConfig['item_per_page']);
		$filters = $this->extractFiltersInRequest($request, $queryMapConfig['filters']);
		
		// Parse pagination request query for sort
		$sortAttributes = RequestParser::parseQueryToArray($request, $queryMapConfig['sort'], $sortConfig['delimiter']);
		$descAttributes = RequestParser::parseQueryToArray($request, $queryMapConfig['desc'], $sortConfig['delimiter']);
		
		foreach ($sortAttributes as $key => $attribute) {
			if (!in_array($attribute, $sortConfig['attributes_availables'])) {
				if ($strictMode === true) {
					throw new SortAttributeNotAvailableException($attribute, sprintf('Invalid request sort attribute "%s" not avalaible.', $attribute));
				}
				continue;
			}
			
			$sortAttributes[$attribute] = in_array($attribute, $descAttributes) ? 'DESC' : 'ASC';
			unset($sortAttributes[$key]);
		}
		
		$this->criteria = empty($filters) ? $criteria : array_merge($criteria, $filters);
		$this->orderBy = empty($sortAttributes) ? $orderBy :  array_merge($orderBy, $sortAttributes);
		
		// prepare db query
		$this->internalCountQuery = $this->createCountQuery($this->criteria);
		$this->internalSelectQuery = $this->createSelectQuery($this->criteria);
		$this->prepared = true;
		
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
		if ($this->prepared === false) {
			throw new \LogicException('Unable to execute "fetch" method without executing "prepare" method');
		}
		
		$items = [];
		$objectRepository = $this->objectManager->getRepository($this->className);
		
		if ($this->countItemsCallable instanceof \Closure) {
			$fn = $this->countItemsCallable;
			$this->fullyItems = $fn($objectRepository, $this->criteria);
			
			if (!is_integer($this->fullyItems)) {
				throw new \UnexpectedValueException('The closure "countItemsCallable" returned an unexcepted value.');
			}
		} elseif ($this->countQuery instanceof \Doctrine\ORM\Query/* || $this->countQuery instanceof \Doctrine\ODM\MongoDB\Query\Query*/) {
			$this->fullyItems = $this->countQuery->getSingleScalarResult();
		} else {
			$this->fullyItems = (int) ($this->internalCountQuery instanceof \Doctrine\ORM\Query ? 
					$this->internalCountQuery->getSingleScalarResult() : $this->internalCountQuery->execute());
		}
		
		if ($this->fullyItems > 0) {
			if ($this->selectItemsCallable instanceof \Closure) {
				$fn = $this->selectItemsCallable;
				$items = $fn($objectRepository, $this->criteria, $this->orderBy, $this->itemPerPage, $this->getItemOffset());
				
				if (!is_array($items)) {
					throw new \UnexpectedValueException('The closure "selectItemsCallable" returned an unexcepted value.');
				}
			} elseif ($this->selectQuery instanceof \Doctrine\ORM\Query) {
				$items = $this->selectQuery->setFirstResult($this->getItemOffset())
											->setMaxResults($this->itemPerPage)
											->getResult();
			} else {
				$items = $this->internalSelectQuery instanceof \Doctrine\ORM\Query ? 
						$this->internalSelectQuery->getResult() : $this->internalSelectQuery->execute()->toArray(false);
			}
		}
		
		// Pagination result set definition
		$paginationResultSet = new PaginationResultSet($this->page, $this->itemPerPage, $this->criteria, $this->orderBy, $this->getItemOffset(), $this->fullyItems, $this->getPageNumber(), $items);
		$this->paginationStore[$this->currentManagerName] = $paginationResultSet->toArray(['items']);
		
		// Add twig global parameters for manager config key
		if ($this->container->getParameter('oka_pagination.twig.enable_global') === true) {
			$twig = $this->container->get('twig');
			$twig->addGlobal(TwigExtension::TWIG_GLOBAL_VAR_NAME, $this->paginationStore);
		}
		
		// reset manager
		$this->reset();
		
		return $hydrationMode == self::HYDRATE_ARRAY ? $paginationResultSet->toArray() : $paginationResultSet;
	}
	
	/**
	 * Get the name of current pagination manager
	 * 
	 * @return string
	 */
	public function getCurrentManagerName()
	{
		return $this->currentManagerName;
	}
	
	/**
	 * Store pagination result set
	 * 
	 * @return array
	 */
	public function getPaginationStore()
	{
		return $this->paginationStore;
	}
	
	/**
	 * Load pagination manager config
	 * 
	 * @param string $managerName
	 * @throws \InvalidArgumentException
	 * @return array
	 */
	protected function loadManagerConfig($managerName)
	{
		$managerConfig = $this->getManagerConfig($managerName);
		
		if (isset($managerConfig['model_manager_name']) && isset($managerConfig['db_driver'])) {
			/** @var \Doctrine\Bundle\DoctrineBundle\Registry $registry */
			$registry = $this->container->get(BundleExtension::$doctrineDrivers[$managerConfig['db_driver']]['registry']);
			$this->objectManager = $registry->getManager($managerConfig['model_manager_name']);
		} else {
			$this->objectManager = $this->container->get('oka_pagination.default.object_manager');
		}
		
		$this->currentManagerName = $managerName;
		$this->className = $managerConfig['class'];
		$this->itemPerPage = $managerConfig['item_per_page'];
		$this->maxPageNumber = $managerConfig['max_page_number'];
		
		return $managerConfig;
	}
	
	/**
	 * @param Request $request
	 * @param string $key
	 */
	protected function extractPageInRequest(Request $request, $key)
	{
		$page = $request->query->has($key) ? $request->query->get($key) : $request->attributes->get($key);
		
		if ($page && preg_match('#^[0-9]+$#', $page)) {
			$this->setPage((int) $page);
		}
	}

	/**
	 * @param Request $request
	 * @param string $key
	 */
	protected function extractItemPerPageInRequest(Request $request, $key)
	{
		$itemPerPage = $request->query->has($key) ? $request->query->get($key) : $request->attributes->get($key);
		
		if ($itemPerPage && preg_match('#^[0-9]+$#', $itemPerPage)) {
			$this->setItemPerPage((int) $itemPerPage);
		}
	}
	
	/**
	 * @param Request $request
	 * @param array $filterMaps
	 * @return mixed[]
	 */
	protected function extractFiltersInRequest(Request $request, array $filterMaps)
	{
		$criteria = [];
		
		foreach ($filterMaps as $key => $filterMap) {
			if (null === ($value = self::getRequestParameter($request, $key))) {
				continue;
			}
			
			if ($filterMap['type'] !== 'datetime') {
				if (!is_string($value) || $filterMap['type'] !== 'string') {
					settype($value, $filterMap['type']);
				}
			} else {
				$value = new \DateTime(is_int($value) ? '@'.$value : $value);
			}
			
			$criteria[$filterMap['field'] ?: $key] = $value;
		}
		
		return $criteria;
	}
	
	/**
	 * @param Request $request
	 * @param string $key
	 * @return mixed
	 */
	protected static function getRequestParameter(Request $request, $key)
	{
		return $request->query->has($key) ? $request->query->get($key) : $request->attributes->get($key);
	}
	
	/**
	 * @param integer $page
	 * @return \Oka\Pagination\Service\Pagination
	 */
	protected function setPage($page)
	{
		$this->page = $this->maxPageNumber < $page ? $this->maxPageNumber : $page;
		return $this;
	}
	
	/**
	 * @return integer
	 */
	protected function getItemOffset()
	{
		return $this->page < 2 ? 
			0 : $this->itemPerPage * ($this->maxPageNumber < $this->page ? $this->maxPageNumber - 1 : $this->page - 1);
	}
	
	/**
	 * @return integer
	 */
	protected function getPageNumber()
	{
		if ($this->pageNumber === null) {
			$this->pageNumber = 0;
			$items = $this->fullyItems - $this->itemPerPage;
			
			while ($items > 0) {
				++$this->pageNumber;
				$items -= $this->itemPerPage;
			}
			
			++$this->pageNumber;
		}
		return $this->pageNumber;
	}
	
	/**
	 * Create internal count items query
	 * 
	 * @param array $criteria
	 * @return \Doctrine\ORM\Query|\Doctrine\ODM\MongoDB\Query\Query
	 */
	protected function createCountQuery(array $criteria = [])
	{
		if ($this->objectManager instanceof \Doctrine\ORM\EntityManager) {
			/** @var \Doctrine\ORM\QueryBuilder $builder */
			$builder = $this->objectManager->createQueryBuilder()
							->select('COUNT(DISTINCT p)')
							->from($this->className, 'p');
		} elseif ($this->objectManager instanceof \Doctrine\ODM\MongoDB\DocumentManager) {
			$builder = $this->objectManager->createQueryBuilder($this->className)
							->count();
		} else {
			throw new ObjectManagerNotSupportedException(sprintf('Doctrine object manager class "%s" is not supported.', get_class($this->objectManager)));
		}
		
		$this->manipulator->applyExprFromArray($builder, 'p', $criteria);
		
		return $builder->getQuery();
	}
	
	/**
	 * Create internal select items query
	 * 
	 * @param array $criteria
	 * @param array $orderBy
	 * @return \Doctrine\ORM\Query|\Doctrine\ODM\MongoDB\DocumentManager
	 */
	protected function createSelectQuery(array $criteria = [])
	{
		if ($this->objectManager instanceof \Doctrine\ORM\EntityManager) {
			$builder = $this->objectManager->createQueryBuilder()
							->select('p')
							->from($this->className, 'p')
							->setFirstResult($this->getItemOffset())
							->setMaxResults($this->itemPerPage);
			
			foreach ($this->orderBy as $key => $value) {
				$builder->orderBy(sprintf('p.%s', $key), $value);
			}
		} elseif ($this->objectManager instanceof \Doctrine\ODM\MongoDB\DocumentManager) {
			$builder = $this->objectManager->createQueryBuilder($this->className)
							->skip($this->getItemOffset())
							->limit($this->itemPerPage);
			
			foreach ($this->orderBy as $key => $value) {
				$builder->sort($key, $value);
			}
		} else {
			throw new ObjectManagerNotSupportedException(sprintf('Doctrine object manager class "%s" is not supported.', get_class($this->objectManager)));
		}
		
		$this->manipulator->applyExprFromArray($builder, 'p', $criteria);
		
		return $builder->getQuery();
	}
	
	/**
	 * Reset pagination manager after fetch
	 */
	protected function reset()
	{
		$this->countQuery = null;
		$this->countItemsCallable = null;
		$this->selectQuery = null;
		$this->selectItemsCallable = null;
		$this->prepared = false;
		$this->countQuery = null;
		$this->selectQuery = null;
		$this->orderBy = [];
		$this->fullyItems = 0;
		$this->pageNumber = null;
	}
}
