<?php
namespace Oka\PaginationBundle\Service;

use Oka\PaginationBundle\DependencyInjection\OkaPaginationExtension as BundleExtension;
use Oka\PaginationBundle\Exception\SortAttributeNotAvailableException;
use Oka\PaginationBundle\Util\PaginationQuery;
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
	/**
	 * @var ContainerInterface $container
	 */
	protected $container;
	
	/**
	 * @var PaginationManagerBag $managerBag
	 */
	protected $managerBag;
	
	/**
	 * @var QueryBuilderHandler $qbHandler
	 */
	protected $qbHandler;
	
	/**
	 * @var array $defaultConfig
	 */
	private $defaultConfig;
	
	/**
	 * @var string $lastManagerName
	 */
	private $lastManagerName;
	
	/**
	 * Constructor.
	 * 
	 * @param ContainerInterface $container
	 * @param PaginationManagerBag $managerBag
	 * @param QueryBuilderHandler $qbHandler
	 * @param array $config
	 */
	public function __construct(ContainerInterface $container, PaginationManagerBag $managerBag, QueryBuilderHandler $qbHandler, array $config)
	{
		if ($diff = array_diff(array_keys($config), ['db_driver', 'model_manager_name', 'item_per_page', 'max_page_number', 'template', 'request'])) {
			throw new \InvalidArgumentException(sprintf('The following configuration are not supported "%s".', implode(', ', $diff)));
		}
		
		$this->container = $container;
		$this->managerBag = $managerBag;
		$this->qbHandler = $qbHandler;
		$this->defaultConfig = $config;
	}
	
	/**
	 * Get pagination manager config
	 * 
	 * @param string $name The pagination manager name
	 * @throws \InvalidArgumentException
	 * @return array
	 */
	public function getManagerConfig($name)
	{
		if (true === $this->managerBag->has($name)) {
			$config = $this->managerBag->get($name);
			
			if (null === $config['template']) {
				$config['template'] = $this->defaultConfig['template'];
			}
		} elseif (true === class_exists($name)) {
			$config = $this->defaultConfig;
			$config['class'] = $name;
		} else {
			throw new \InvalidArgumentException(sprintf('The "%s" configuration key is not attached to a pagination manager.', $name));
		}
		
		return $config;
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
	 * @return \Oka\PaginationBundle\Util\PaginationResultSet
	 */
	public function paginate($managerName, Request $request, array $criteria = [], array $orderBy = [], $strictMode = true, $hydrationMode = PaginationQuery::HYDRATE_OBJECT)
	{
		$query = $this->createQuery($managerName, $request, $criteria, $orderBy, $strictMode);
		
		return $query->fetch($hydrationMode);
	}
	
	/**
	 * Create pagination query
	 * 
	 * @param string $managerName
	 * @param Request $request
	 * @param array $criteria
	 * @param array $orderBy
	 * @param string $strictMode
	 * @throws SortAttributeNotAvailableException
	 * @return \Oka\PaginationBundle\Util\PaginationQuery
	 */
	public function createQuery($managerName, Request $request, array $criteria = [], array $orderBy = [], $strictMode = true)
	{
		$this->lastManagerName = $managerName;
		$config = $this->getManagerConfig($managerName);
		$options = [
			'twig_extension_enabled' => $this->container->getParameter('oka_pagination.twig.enable_extension'),
			'strict_mode' => $strictMode
		];
		
		$sortConfig = $config['request']['sort'];
		$queryMapConfig = $config['request']['query_map'];
		
		// Extract pagination data in request
		$page = RequestParser::extractPageInRequest($request, $queryMapConfig['page'], 1);
		$config['item_per_page'] = RequestParser::extractItemPerPageInRequest($request, $queryMapConfig['item_per_page'], $config['item_per_page']);
		$filters = RequestParser::extractFiltersInRequest($request, $queryMapConfig['filters']);
		
		// Parse pagination request query for sort
		$sortAttributes = RequestParser::parseQueryToArray($request, $queryMapConfig['sort'], $sortConfig['delimiter']);
		$descAttributes = RequestParser::parseQueryToArray($request, $queryMapConfig['desc'], $sortConfig['delimiter']);
		
		foreach ($sortAttributes as $key => $attribute) {
			if (!in_array($attribute, $sortConfig['attributes_availables'])) {
				if (true === $strictMode) {
					throw new SortAttributeNotAvailableException($attribute, sprintf('Invalid request sort attribute "%s" not avalaible.', $attribute));
				}
				continue;
			}
			
			$sortAttributes[$attribute] = in_array($attribute, $descAttributes) ? 'DESC' : 'ASC';
			unset($sortAttributes[$key]);
		}
		
		$criteria = empty($filters) ? $criteria : array_merge($criteria, $filters);
		$orderBy = empty($sortAttributes) ? $orderBy : array_merge($orderBy, $sortAttributes);
		
		if ($config['db_driver'] !== $this->defaultConfig['db_driver'] || $config['model_manager_name'] !== $this->defaultConfig['model_manager_name']) {
			/** @var \Doctrine\Bundle\DoctrineBundle\Registry $registry */
			$registry = $this->container->get(BundleExtension::$doctrineDrivers[$config['db_driver']]['registry']);
			$objectManager = $registry->getManager($config['model_manager_name']);
		} else {
			$objectManager = $this->container->get('oka_pagination.default.object_manager');
		}
		
		$query = new PaginationQuery($objectManager, $this->qbHandler, null, $managerName, $options, $config, $page, $criteria, $orderBy);
		$query->addQueryPart('select', RequestParser::parseQueryToArray($request, 'fields', ',', []));
		$query->addQueryPart('distinct', (boolean) RequestParser::getRequestParameterValue($request, 'distinct', true));
		
		if (true === $options['twig_extension_enabled']) {
			$query->setTwig($this->container->get('twig'));
		}
		
		return $query;
	}
	
	/**
	 * Get the last manager name
	 * 
	 * @return string
	 */
	public function getLastManagerName()
	{
		return $this->lastManagerName;
	}
}
