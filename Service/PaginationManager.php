<?php
namespace Oka\PaginationBundle\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Query;
use Oka\PaginationBundle\DependencyInjection\OkaPaginationExtension;
use Oka\PaginationBundle\Exception\ObjectManagerNotSupportedException;
use Oka\PaginationBundle\Exception\SortAttributeNotAvailableException;
use Oka\PaginationBundle\Util\PaginationResultSet;
use Oka\PaginationBundle\Util\RequestParser;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * 
 * @author cedrick
 * 
 */
class PaginationManager extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
	const DEFAULT_TEMPLATE = 'OkaPaginationBundle:Pagination:paginate.html.twig';
	
	const HYDRATE_OBJECT = 0;
	const HYDRATE_ARRAY = 1;
	
	/**
	 * @var ObjectManager $_objectManager
	 */
	private $_objectManager;
	
	/**
	 * @var ContainerInterface $container
	 */
	protected $container;
	
	/**
	 * @var ObjectManager $objectManager
	 */
	protected $objectManager;
	
	/**
	 * @var PaginationBag $paginationBag
	 */
	protected $paginationBag;
	
	/**
	 * @var integer $itemPerPage
	 */
	protected $itemPerPage;
	
	/**
	 * @var integer $maxPageNumber
	 */
	protected $maxPageNumber;
	
	/**
	 * @var string $template
	 */
	protected $template;
	
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
	 * @param ObjectManager $objectManager
	 * @param PaginationBag $paginationBag
	 * @param integer $itemPerPage
	 * @param integer $maxPageNumber
	 * @param string $template
	 */
	public function __construct(ContainerInterface $container, ObjectManager $objectManager, PaginationBag $paginationBag, $itemPerPage, $maxPageNumber, $template = null)
	{
		$this->container = $container;
		$this->objectManager = $objectManager;
		$this->paginationBag = $paginationBag;
		
		$this->itemPerPage = $itemPerPage;
		$this->maxPageNumber = $maxPageNumber;
		$this->template = $template;
		
		$this->page = 1;
		$this->orderBy = [];
		$this->fullyItems = 0;
	}
	
	/**
	 * Load pagination config
	 * 
	 * @param string $key
	 * @throws \InvalidArgumentException
	 * @return array
	 */
	public function loadConfig($key)
	{
		if (!$this->paginationBag->has($key)) {
			throw new \InvalidArgumentException(sprintf('Configuration key "%s" is not defined in pagination bag.', $key));
		}
		
		if ($config = $this->paginationBag->get($key, [])) {
			$this->className = $config['class'];
			$this->itemPerPage = $config['item_per_page'];
			$this->maxPageNumber = $config['max_page_number'];
			
			if (isset($config['template']) && $config['template']) {
				$this->template = $config['template'];
			}
			if (isset($config['model_manager_name']) && $config['model_manager_name']) {
				/** @var \Doctrine\Bundle\DoctrineBundle\Registry $registry */
				$registry = $this->container->get(OkaPaginationExtension::$doctrineDrivers[$config['db_driver']]['registry']);
				$this->objectManager = $registry->getManager($config['model_manager_name']);
			}
		}
		
		return $config;
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
	 * @param integer $page
	 * @return \Oka\Pagination\Service\Pagination
	 */
	protected function setPage($page)
	{
		$this->page = $this->maxPageNumber < $page ? $this->maxPageNumber : $page;
		return $this;
	}
	
	/**
	 * @param integer $itemPerPage
	 * @return \Oka\Pagination\Service\Pagination
	 */
	protected function setItemPerPage($itemPerPage)
	{
		$this->itemPerPage = $itemPerPage;
		return $this;
	}
	
	/**
	 * @return integer
	 */
	protected function getItemOffset()
	{
		if ($this->page < 2) {
			return 0;
		}
		
		return $this->itemPerPage * ($this->maxPageNumber < $this->page ? $this->maxPageNumber - 1 : $this->page - 1);
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
	 * @return array
	 */
	public function getOrderBy() {
		return $this->orderBy;
	}
	
	/**
	 * @return integer
	 */
	public function getPageNumber()
	{
		if ($this->pageNumber !== null) {
			return $this->pageNumber;
		}
		
		$this->pageNumber = 0;
		$items = $this->fullyItems - $this->itemPerPage;
		
		while ($items > 0) {
			++$this->pageNumber;
			$items -= $this->itemPerPage;
		}
		
		return ++$this->pageNumber;
	}
	
	/**
	 * Paginate query
	 * 
	 * @param string $key
	 * @param Request $request
	 * @param array $criteria
	 * @param array $orderBy
	 * @param integer $hydrationMode
	 * @return PaginationResultSet
	 */
	public function paginate($key, Request $request, array $criteria = [], array $orderBy = [], $hydrationMode = self::HYDRATE_OBJECT)
	{
		return $this->prepare($key, $request, $criteria, $orderBy)
					->fetch($hydrationMode);
	}
	
	/**
	 * Prepare pagination query
	 * 
	 * @param string $key
	 * @param Request $request
	 * @param array $criteria
	 * @param array $orderBy
	 * @throws SortAttributeNotAvailableException
	 * @return \Oka\PaginationBundle\Service\PaginationManager
	 */
	public function prepare($key, Request $request, array $criteria = [], array $orderBy = [])
	{
		// Load entity pagination config
		$config = $this->loadConfig($key);
		
		$queryMapConfig = $config['request']['query_map'];
		$sortConfig = $config['sort'];
		
		$query = $request->query;
		$intRegex = '#^[0-9]+$#';
		
		// Parse pagination request query for page
		if ($query->has($queryMapConfig['page'])) {
			if (preg_match('#^[0-9]+$#', ($page = $query->get($queryMapConfig['page'])))) {
				$this->setPage((int) $page);
			}
		}
		if ($query->has($queryMapConfig['item_per_page'])) {
			if (preg_match($intRegex, ($itemPerPage = $query->get($queryMapConfig['item_per_page'])))) {
				$this->setItemPerPage((int) $itemPerPage);
			}
		}
		
		// Parse pagination request query for sort
		$sortAttributes = RequestParser::parseQueryToArray($request, $queryMapConfig['sort'], $sortConfig['delimiter']);
		$descAttributes = RequestParser::parseQueryToArray($request, $queryMapConfig['desc'], $sortConfig['delimiter']);
		
		foreach ($sortAttributes as $key => $attribute) {
			if (!in_array($attribute, $sortConfig['attributes_availables'])) {
				throw new SortAttributeNotAvailableException($attribute, sprintf('Invalid request sort attribute "%s" not avalaible.', $attribute));
			}
			
			$sortAttributes[$attribute] = in_array($attribute, $descAttributes) ? 'DESC' : 'ASC';
			unset($sortAttributes[$key]);
		}
		
		$this->orderBy = !empty($sortAttributes) ? array_merge($orderBy, $sortAttributes) : $orderBy;
		
		// prepare db query
		$this->internalCountQuery = $this->createCountQuery($criteria);
		$this->internalSelectQuery = $this->createSelectQuery($criteria);
		$this->prepared = true;
		
		return $this;
	}
	
	/**
	 * Fetch page
	 * 
	 * @param integer $hydrationMode
	 */
	public function fetch($hydrationMode = self::HYDRATE_OBJECT)
	{
		if ($this->prepared === false) {
			throw new \LogicException('Unable to execute "fetch" method without executing "prepare" method');
		}
		
		$items = [];
		$er = $this->objectManager->getRepository($this->className);
		
		if ($this->countItemsCallable instanceof \Closure) {
			$fn = $this->countItemsCallable;
			$this->fullyItems = $fn($er);
		} elseif ($this->countQuery instanceof \Doctrine\ORM\Query/* || $this->countQuery instanceof \Doctrine\ODM\MongoDB\Query\Query*/) {
			$this->fullyItems = $this->countQuery->getSingleScalarResult();
		} else {
			if ($this->internalCountQuery instanceof \Doctrine\ORM\Query) {
				$this->fullyItems = $this->internalCountQuery->getSingleScalarResult();
			} else {
				$this->fullyItems = $this->internalCountQuery->execute();
			}
		}
		
		if ($this->fullyItems > 0) {
			if ($this->selectItemsCallable instanceof \Closure) {
				$fn = $this->selectItemsCallable;
				$items = $fn($er, $this->orderBy, $this->itemPerPage, $this->getItemOffset());
			} elseif ($this->selectQuery instanceof \Doctrine\ORM\Query) {
				$items = $this->selectQuery->setFirstResult($this->getItemOffset())
											->setMaxResults($this->itemPerPage)
											->getResult();
			} else {
				if ($this->internalSelectQuery instanceof \Doctrine\ORM\Query) {
					$items = $this->internalSelectQuery->getResult();
				} else {
					$items = $this->internalSelectQuery->execute()->toArray(false);
				}
			}			
		}
		// Pagination result set definition
		$paginationResultSet = new PaginationResultSet($this->page, $this->itemPerPage, $this->orderBy, $this->fullyItems, $this->getPageNumber(), $items);
		
		// reset manager
		$this->reset();
		
		return $hydrationMode == self::HYDRATE_ARRAY ? $paginationResultSet->toArray() : $paginationResultSet;
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
			$builder = $this->objectManager->createQueryBuilder()
							->select('COUNT(DISTINCT p)')
							->from($this->className, 'p');
			
			foreach ($criteria as $key => $value) {
				$builder->andWhere(sprintf('p.%1$s = :%1$s', $key))
						->setParameter($key, $value);
			}
		} elseif ($this->objectManager instanceof \Doctrine\ODM\MongoDB\DocumentManager) {
			$builder = $this->objectManager->createQueryBuilder($this->className)
							->count();
			
			foreach ($criteria as $key => $value) {
				$builder->field($key)->equals($value);
			}
		} else {
			throw new ObjectManagerNotSupportedException(sprintf('Doctrine object manager class "%s" is not supported.', get_class($this->objectManager)));
		}
		
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
			
			foreach ($criteria as $key => $value) {
				$builder->andWhere(sprintf('p.%1$s = :%1$s', $key))
						->setParameter($key, $value);
			}
			foreach ($this->orderBy as $key => $value) {
				$builder->orderBy(sprintf('p.%s', $key), $value);
			}
		} elseif ($this->objectManager instanceof \Doctrine\ODM\MongoDB\DocumentManager) {
			$skip = $this->getItemOffset();
			$builder = $this->objectManager->createQueryBuilder($this->className)
// 							->sort($this->orderBy)
							->skip($skip ? ($skip+1) : 0)
							->limit($this->itemPerPage);
			
			foreach ($criteria as $key => $value) {
				$builder->field($key)->equals($value);
			}
			foreach ($this->orderBy as $key => $value) {
				$builder->sort($key, $value);
			}
		} else {
			throw new ObjectManagerNotSupportedException(sprintf('Doctrine object manager class "%s" is not supported.', get_class($this->objectManager)));
		}
		
		return $builder->getQuery();
	}
	
	private function reset()
	{
		$this->prepared = false;
		$this->countQuery = null;
		$this->selectQuery = null;
		$this->orderBy = [];
		$this->fullyItems = 0;
		$this->pageNumber = null;
	}
	
	public function getName()
	{
		return 'oka_pagination.twig_extension';
	}
	
	public function getGlobals()
	{
		return [
			'oka_pagination' => [
				'page' 			=> $this->page,
				'itemPerPage' 	=> $this->itemPerPage,
				'fullyItems' 	=> $this->fullyItems,
				'pageNumber' 	=> $this->getPageNumber()
		]];
	}
	
	public function renderBlock(\Twig_Environment $env, $route, array $params = [])
	{
		return $env->render($this->template ?: self::DEFAULT_TEMPLATE, ['route' => $route, 'params' => $params]);
	}
	
	public function getFunctions()
	{
		return [
			new \Twig_SimpleFunction('paginate', [$this, 'renderBlock'], ['needs_environment' => true, 'is_safe' => ['html']]),
		];
	}
}