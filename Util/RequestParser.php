<?php
namespace Oka\PaginationBundle\Util;

use Symfony\Component\HttpFoundation\Request;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
final class RequestParser
{
	/**
	 * @param Request $request
	 * @param string $key
	 * @param string $delimiter
	 * @param mixed $defaultValue
	 * @return mixed|[]
	 */
	public static function parseQueryToArray(Request $request, $key, $delimiter = null, $defaultValue = null) {
		$value = $request->query->get($key, $defaultValue);
		
		if ($value && $delimiter !== null) {
			$value = array_map(function($value){
				return self::sanitizeQuery($value);
			}, explode($delimiter, $value));
		}
		
		return $value ?: [];
	}
	
	/**
	 * Sanitize request query value
	 * Decode et trim value
	 * 
	 * @param stirng $query
	 * @return string
	 */
	public static function sanitizeQuery($query) {
		return trim(rawurldecode($query));
	}
}
