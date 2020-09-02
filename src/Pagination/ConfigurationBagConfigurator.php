<?php
namespace Oka\PaginationBundle\Pagination;

use Symfony\Component\Routing\Route;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
final class ConfigurationBagConfigurator
{
	private $paginationManagers;
	
	public function __construct(array $paginationManagers)
	{
		$this->paginationManagers = $paginationManagers;
	}
	
	public function __invoke(ConfigurationBag $configurations)
	{
		foreach ($this->paginationManagers as $key => $manager) {
			$configurations->set($key, new Configuration(
				$manager['db_driver'],
				$manager['item_per_page'],
				$manager['max_page_number'],
				$manager['sort'],
				$manager['query_mappings'],
				$this->createFilterBag($manager['filters']),
				$manager['object_manager_name'],
				$manager['class'] ?? null,
				true === isset($manager['route']) && true === $manager['route']['enabled'] ?
					new Route(
						$manager['route']['path'],
						$manager['route']['defaults'],
						$manager['route']['requirements'],
						$manager['route']['options'],
						$manager['route']['host'],
						$manager['route']['schemes'],
						$manager['route']['methods'],
						$manager['route']['condition']
					) : null
			));
		}
	}
	
	protected function createFilterBag(array $filters) :FilterBag
	{
		$bag = new FilterBag();
		
		foreach ($filters as $key => $filter) {
			$bag->set($key, new Filter($filter['property_name'], $filter['cast_type'], $filter['searchable'], $filter['ordering']));
		}
		
		return $bag;
	}
}

