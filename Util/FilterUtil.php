<?php
namespace Oka\PaginationBundle\Util;

use Oka\PaginationBundle\Converter\AbstractQueryExprConverter;
use Oka\PaginationBundle\Exception\BadQueryExprException;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
final class FilterUtil
{
	/**
	 * @param mixed $value
	 * @param string $type
	 * @return mixed
	 */
	public static function castTo($value, $type)
	{
		if ($type !== 'datetime') {
			if (!is_string($value) && $type !== 'string') {
				settype($value, $type);
			}
		} else {
			$value = new \DateTime(is_int($value) ? '@'.$value : $value);
		}
		
		return $value;
	}
}