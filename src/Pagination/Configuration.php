<?php
namespace Oka\PaginationBundle\Pagination;

use Symfony\Component\Routing\Route;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
final class Configuration
{
	private $dbDriver;
	private $itemPerPage;
	private $maxPageNumber;
	private $sortConfig;
	private $queryMappings;
	private $filters;
	private $objectManagerName;
	private $className;
	private $route;
	
	public function __construct(string $dbDriver, int $itemPerPage, int $maxPageNumber, array $sortConfig, array $queryMappings, FilterBag $filters, string $objectManagerName = null, string $className = null, Route $route = null)
	{
		$this->dbDriver = $dbDriver;
		$this->itemPerPage = $itemPerPage;
		$this->maxPageNumber = $maxPageNumber;
		$this->sortConfig = $sortConfig;
		$this->filters = $filters;
		$this->objectManagerName = $objectManagerName;
		$this->className = $className;
		$this->queryMappings = $queryMappings;
		$this->route = $route;
	}
	
	public function getDBDriver() :string
	{
		return $this->dbDriver;
	}
	
	public function getItemPerPage() :int
	{
		return $this->itemPerPage;
	}
	
	public function getMaxPageNumber() :int
	{
		return $this->maxPageNumber;
	}
	
	public function setMaxPageNumber(int $maxPageNumber) :self
	{
		$this->maxPageNumber = $maxPageNumber;
		return $this;
	}
	
	public function getSortConfig() :array
	{
		return $this->sortConfig;
	}
	
	public function getQueryMappings() :array
	{
		return $this->queryMappings;
	}
	
	public function getFilters() :FilterBag
	{
		return $this->filters;
	}
	
	public function getObjectManagerName() :?string
	{
		return $this->objectManagerName;
	}
	
	public function getClassName() :?string
	{
		return $this->className;
	}
	
	public function getRoute() :?Route
	{
		return $this->route;
	}
}
