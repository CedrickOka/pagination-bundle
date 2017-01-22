<?php
namespace Oka\PaginationBundle\Util;

/**
 * @author cedrick
 *
 */
class PaginationResultSet
{
	/**
	 * @var integer $page
	 */
	protected $page;
	
	/**
	 * @var integer $itemPerPage
	 */
	protected $itemPerPage;
	
	/**
	 * @var array $orderBy
	 */
	protected $orderBy;
	
	/**
	 * @var integer $fullyItems
	 */
	protected $fullyItems;
	
	/**
	 * @var array
	 */
	protected $items;
	
	/**
	 * @var integer $pageNumber
	 */
	protected $pageNumber;
	
	public function __construct($page, $itemPerPage, array $orderBy, $fullyItems, $pageNumber, array $items)
	{
		$this->page = $page;
		$this->itemPerPage = $itemPerPage;
		
		$this->orderBy = $orderBy;		
		$this->fullyItems = $fullyItems;
		$this->items = $items;
	}
	
	/**
	 * @return integer
	 */
	public function getPage()
	{
		return $this->page;
	}
	
	/**
	 * @return integer
	 */
	public function getItemPerPage()
	{
		return $this->itemPerPage;
	}
	
	/**
	 * @return array
	 */
	public function getOrderBy()
	{
		return $this->orderBy;
	}
	
	/**
	 * @return integer
	 */
	public function getFullyItems()
	{
		return $this->fullyItems;
	}
	
	/**
	 * @return array
	 */
	public function getItems()
	{
		return $this->items;
	}
	
	/**
	 * @return integer
	 */
	public function getPageNumber()
	{
		return $this->pageNumber;
	}
}