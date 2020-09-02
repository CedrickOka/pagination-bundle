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
		$filters = $configuration->getFilters();
		$queryMappings = $configuration->getQueryMappings();
		$sortConfig = $configuration->getSortConfig();
		
		// Extract pagination criteria and sort in request
		$_criteria = [];
		$_orderBy = [];
		$sortAttributes = $this->parseQueryToArray($request, $queryMappings['sort'], $sortConfig['delimiter']);
		$descAttributes = $this->parseQueryToArray($request, $queryMappings['desc'], $sortConfig['delimiter']);
		
		/** @var \Oka\PaginationBundle\Pagination\Filter $filter */
		foreach ($filters as $key => $filter) {
			if (true === $filter->isSearchable()) {
				if (null === ($value = $request->get($key))) {
					continue;
				}
				
				$criteria[$key] = $value;
			}
			
			$ordering = $filter->getOrdering();
			
			if (false === $ordering['enabled']) {
				continue;
			}
			
			if (false === in_array($key, $sortAttributes)) {
				continue;
			}
			
			$_orderBy[$key] = true === in_array($key, $descAttributes) ? 'DESC' : $ordering['direction'];
			unset($sortAttributes[$key]);
		}
		
		if (false === empty($sortAttributes)) {
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
			(int) $request->get($queryMappings['item_per_page'], $configuration->getItemPerPage()), 
			$configuration->getMaxPageNumber(), 
			$filters,
			(int) $request->get($queryMappings['page'], 1),
			true === empty($_criteria) ? $criteria : array_merge($criteria, $_criteria),
			true === empty($_orderBy) ? $orderBy : array_merge($orderBy, $_orderBy)
		);
		
		$query->addQueryPart('select', $this->parseQueryToArray($request, $queryMappings['fields'], ',', []));
		$query->addQueryPart('distinct', (boolean) $request->get($queryMappings['distinct'], true));
		
		return $query;
	}
	
	protected function parseQueryToArray(Request $request, string $key, $delimiter = null, $defaultValue = null)
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
