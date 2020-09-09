<?php
namespace Oka\PaginationBundle\Pagination;

use Oka\PaginationBundle\Exception\SortAttributeNotAvailableException;
use Oka\PaginationBundle\Pagination\FilterExpression\FilterExpressionHandler;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class PaginationManager
{
	private $registryLocator;
	private $configurations;
	private $filterHandler;
	
	public function __construct(ServiceLocator $registryLocator, ConfigurationBag $configurations, FilterExpressionHandler $filterHandler)
	{
		$this->registryLocator = $registryLocator;
		$this->configurations = $configurations;
		$this->filterHandler = $filterHandler;
	}
	
	public function getConfiguration(string $key) :Configuration
	{
		if (true === $this->configurations->has($key)) {
			return $this->configurations->get($key);
		}
		
		if (true === class_exists($key)) {
			return $this->configurations->getDefaults();
		}
		
		throw new \InvalidArgumentException(sprintf('The "%s" configuration key is not attached to a pagination manager.', $key));
	}
	
	public function paginate(string $key, Request $request, array $criteria = [], array $orderBy = [], bool $strictMode = true, $hydrationMode = Query::HYDRATE_OBJECT)
	{
		$query = $this->createQuery($key, $request, $criteria, $orderBy, $strictMode);
		
		return $query->fetch($hydrationMode);
	}
	
	public function createQuery(string $key, Request $request, array $criteria = [], array $orderBy = [], bool $strictMode = true) :Query
	{
		$configuration = $this->getConfiguration($key);
		
		$queryMappings = $configuration->getQueryMappings();
		$filters = $configuration->getFilters();
		$sort = $configuration->getSort();
		
		// Extract pagination criteria and sort in request
		$_criteria = [];
		$_orderBy = [];
		$sortAttributes = $this->parseQueryToArray($request, $queryMappings['sort'], $sort['delimiter']);
		$descAttributes = $this->parseQueryToArray($request, $queryMappings['desc'], $sort['delimiter']);
		
		/** @var \Oka\PaginationBundle\Pagination\Filter $filter */
		foreach ($filters as $key => $filter) {
			if (true === $filter->isSearchable()) {
				if (null === ($value = $request->get($key))) {
					continue;
				}
				
				$criteria[$filter->getPropertyName()] = $value;
			}
			
			if (false === $filter->isOrderable()) {
				continue;
			}
			
			if (false === in_array($key, $sortAttributes) && false === isset($sort['order'][$key])) {
				continue;
			}
			
			$_orderBy[$filter->getPropertyName()] = true === in_array($key, $descAttributes) ? 'DESC' : $sort['order'][$key] ?? 'ASC';
			
			if (false !== ($key = array_search($key, $sortAttributes))) {
				unset($sortAttributes[$key]);
			}
		}
		
		if (true === $strictMode && false === empty($sortAttributes)) {
			throw new SortAttributeNotAvailableException($sortAttributes, sprintf('Invalid request sort attributes "%s" not avalaible.', implode(', ', $sortAttributes)));
		}
		
		/** @var \Doctrine\Persistence\ManagerRegistry $registry */
		$registry = $this->registryLocator->get($configuration->getDBDriver());
		/** @var \Doctrine\Persistence\ObjectManager $objectManager */
		$objectManager = $registry->getManager($configuration->getObjectManagerName());
		
		$query = new Query(
			$objectManager, 
			$this->filterHandler, 
			$configuration->getClassName(), 
			(int) $request->get($queryMappings['item_per_page'], (string) $configuration->getItemPerPage()), 
			$configuration->getMaxPageNumber(), 
			$filters,
			(int) $request->get($queryMappings['page'], '1'),
			true === empty($_criteria) ? $criteria : array_merge($criteria, $_criteria),
			true === empty($_orderBy) ? $orderBy : array_merge($orderBy, $_orderBy)
		);
		
		$query->addQueryPart('select', $this->parseQueryToArray($request, $queryMappings['fields'], ',', []));
		$query->addQueryPart('distinct', $request->query->has($queryMappings['distinct']));
		
		return $query;
	}
	
	protected function parseQueryToArray(Request $request, string $key, string $delimiter = null, $defaultValue = null)
	{
		$value = $request->query->get($key, $defaultValue);
		
		if ($value && null !== $delimiter) {
			$value = array_map(function($value){
				return $this->sanitizeQuery($value);
			}, explode($delimiter, $value));
		}
		
		return $value ?: [];
	}
	
	protected function sanitizeQuery(string $query) :string
	{
		return trim(rawurldecode($query));
	}
}
