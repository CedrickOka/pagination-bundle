<?php
namespace Oka\PaginationBundle\Util;

/**
 * 
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
	 * @var array $filters
	 */
	protected $filters;
	
	/**
	 * @var array $orderBy
	 */
	protected $orderBy;
	
	/**
	 * @var integer $itemOffset
	 */
	protected $itemOffset;
	
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
	
	public function __construct($page, $itemPerPage, array $filters, array $orderBy, $itemOffset, $fullyItems, $pageNumber, $items)
	{
		$this->page = $page;
		$this->itemPerPage = $itemPerPage;
		$this->filters = $filters;
		$this->orderBy = $orderBy;
		$this->itemOffset = $itemOffset;
		$this->fullyItems = $fullyItems;
		$this->pageNumber = $pageNumber;
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
	public function getFilters()
	{
		return $this->filters;
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
	public function getItemOffset() {
		return $this->itemOffset;
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
	
	/**
	 * @return mixed[]
	 */
	public function toArray() {
		return [
				'page' => $this->page,
				'itemPerPage' => $this->itemPerPage,
				'orderBy' => $this->orderBy,
				'itemOffset' => $this->itemOffset,
				'fullyItems' => $this->fullyItems,
				'pageNumber' => $this->pageNumber,
				'items' => $this->items
		];
	}
}