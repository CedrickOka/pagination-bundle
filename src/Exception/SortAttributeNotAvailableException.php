<?php
namespace Oka\PaginationBundle\Exception;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class SortAttributeNotAvailableException extends PaginationException
{
	/**
	 * @var string $sort
	 */
	private $sort;
	
	public function __construct($sort, $message = null, $code = null, $previous = null) {
		parent::__construct($message, $code, $previous);
		
		$this->sort = $sort;
	}
	
	/**
	 * @return string
	 */
	public function getSort() {
		return $this->sort;
	}
}
