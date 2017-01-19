<?php
namespace Oka\PaginationBundle\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Oka\PaginationBundle\Util\PaginationResultSet;
use Oka\PaginationBundle\Util\RequestParser;
use Oka\PaginationBundle\Util\SortAttributeNotAvailableException;
use Symfony\Component\HttpFoundation\Request;

/**
 * 
 * @author cedrick
 * 
 */
class PaginationManager extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
	const DEFAULT_TEMPLATE = 'OkaPaginationBundle:Pagination:paginate.html.twig';
	
	const HYDRATE_ITEMS = 0;
	const HYDRATE_PAGE = 1;
	
	/**
	 * @var Registry $doctrine
	 */
	protected $doctrine;
	
	/**
	 * @var EntityManager $entityManager
	 */
	protected $entityManager;
	
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
	 * @var array $orderBy
	 */
	protected $orderBy;
	
	/**
	 * @var Query $selectQuery
	 */
	protected $selectQuery;

	/**
	 * @var Query $countQuery
	 */
	protected $countQuery;
	
	/**
	 * @var integer $fullyItems
	 */
	protected $fullyItems;
	
	/**
	 * @param Registry $doctrine
	 * @param EntityManager $entityManager
	 * @param PaginationBag $paginationBag
	 * @param integer $itemPerPage
	 * @param integer $maxPageNumber
	 * @param string $template
	 */
	public function __construct(Registry $doctrine, EntityManager $entityManager, PaginationBag $paginationBag, $itemPerPage, $maxPageNumber, $template = null)
	{
		$this->doctrine = $doctrine;
		$this->entityManager = $entityManager;
		$this->paginationBag = $paginationBag;
		
		$this->itemPerPage = $itemPerPage;
		$this->maxPageNumber = $maxPageNumber;
		$this->template = $template;
		
		$this->page = 1;
		$this->orderBy = [];
		$this->fullyItems = 0;
	}
	
	public function loadConfig($key)
	{
		if (!$this->paginationBag->has($key)) {
			throw new \InvalidArgumentException(sprintf('Configuration key "%s" is not defined in pagination bag.', $key));
		}
		
		if ($bag = $this->paginationBag->get($key, [])) {
			$this->className = $bag['class'];
			$this->itemPerPage = $bag['item_per_page'];
			$this->maxPageNumber = $bag['max_page_number'];
			
			if (isset($bag['template']) && $bag['template']) {
				$this->template = $bag['template'];
			}
			if (isset($bag['entity_manager_name']) && $bag['entity_manager_name']) {
				$this->entityManager = $this->doctrine->getManager($bag['entity_manager_name']);
			}
		}
		
		return $bag;
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
	 * @return array
	 */
	public function getOrderBy() {
		return $this->orderBy;
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
	 * @return integer
	 */
	protected function getItemLimit()
	{
		return $this->itemPerPage;
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
	public function paginate($key, Request $request, array $criteria = [], array $orderBy = [], $hydrationMode = self::HYDRATE_ITEMS)
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
		
		$queryMapConfig = $config['requet']['query_map'];
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
		$sortAttributes = RequestParser::parseQuerytoArray($request, $queryMapConfig['sort'], $sortConfig['delimiter']);
		$descAttributes = RequestParser::parseQuerytoArray($request, $queryMapConfig['desc'], $sortConfig['delimiter']);
		
		foreach ($sortAttributes as $key => $attribute) {
			if (!in_array($attribute, $sortConfig['attributes_availables'])) {
				throw new SortAttributeNotAvailableException(sprintf('Invalid request sort attribute "%s" not avalaible.', $attribute));
			}
			
			$sortAttributes[$attribute] = in_array($attribute, $descAttributes) ? 'DESC' : 'ASC';
			unset($sortAttributes[$key]);
		}
		
		$this->orderBy = !empty($sortAttributes) ? array_merge($orderBy, $sortAttributes) : $orderBy;
		
		// Prepare database query
		if ($this->countQuery === null) {
			$this->countQuery = $this->createCountQuery($criteria);
		}
		if ($this->selectQuery === null) {
			$this->selectQuery = $this->createSelectQuery($criteria, $this->orderBy);
		}
		
		$this->selectQuery->setFirstResult($this->getItemOffset())
						  ->setMaxResults($this->getItemLimit());
		
		return $this;
	}
	
	/**
	 * Fetch page
	 * 
	 * @param integer $hydrationMode
	 */
	public function fetch($hydrationMode = self::HYDRATE_ITEMS)
	{
		$items = [];
		$this->fullyItems = $this->countQuery->getSingleScalarResult();
		
		if ($this->fullyItems > 0) {
			$items = $this->selectQuery->getResult();
		}
		
		return new PaginationResultSet($this->page, $this->itemPerPage, $this->orderBy, $this->fullyItems, $items);
	}
	
	protected function createCountQuery(array $criteria = [])
	{
		$query = $this->entityManager->createQueryBuilder();
		$query->select('COUNT(DISTINCT p)')
			  ->from($this->className, 'p');
		
		foreach ($criteria as $key => $value) {
			$query->andWhere(sprintf('p.%1$s = :%1$s', $key));
			$query->setParameter($key, $value);
		}
		
		return $query->getQuery();
	}
	
	protected function createSelectQuery(array $criteria = [], array $orderBy = [])
	{
		$query = $this->entityManager->createQueryBuilder();
		$query->select('p')
			  ->from($this->className, 'p');
		
		foreach ($criteria as $key => $value) {
			$query->andWhere(sprintf('p.%1$s = :%1$s', $key));
			$query->setParameter($key, $value);
		}
		foreach ($orderBy as $key => $value) {
			$query->orderBy(sprintf('p.%s', $key), $value);
		}
		
		return $query->getQuery();
	}
	
	public function getListItemsWithQuery(array $params = [], array $sort = [])
	{
		return $this->objectManager->getRepository($this->class)->findBy($params, $sort, $this->itemPerPage, $this->getItemOffset());
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
				'pageNumber' 	=> $this->getPageNumber(),
				'fullyItems' 	=> $this->getFullyItems()
		]];
	}
	
	public function renderBlock(\Twig_Environment $env, $route, array $params = [])
	{
		return $env->render($this->template ?: self::DEFAULT_TEMPLATE, ['route' => $route, 'params' => $params]);
	}
	
	public function getFunctions()
	{
		return [
			new \Twig_SimpleFunction('pagination', [$this, 'renderBlock'], ['needs_environment' => true, 'is_safe' => ['html']]),
		];
	}
}