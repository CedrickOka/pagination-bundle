<?php
namespace Oka\PaginationBundle\Util;

/**
 * 
 * @author cedrick
 * 
 */
class SortAttributeNotAvailableException extends \Exception
{
	public function __construct($message = null, $code = null, $previous = null) {
		parent::__construct($message, $code, $previous);
	}
}