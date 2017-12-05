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
	 * @param mixed $defaultValue
	 * @return int
	 */
	public static function extractPageInRequest(Request $request, $key, $defaultValue = 1)
	{
		return (int) self::getRequestParameterValue($request, $key, $defaultValue);
	}

	/**
	 * @param Request $request
	 * @param string $key
	 * @param mixed $defaultValue
	 * @return int
	 */
	public static function extractItemPerPageInRequest(Request $request, $key, $defaultValue = 10)
	{
		return (int) self::getRequestParameterValue($request, $key, $defaultValue);
	}
	
	/**
	 * @param Request $request
	 * @param array $filterMaps
	 * @return mixed[]
	 */
	public static function extractFiltersInRequest(Request $request, array $filterMaps)
	{
		$criteria = [];
		
		foreach ($filterMaps as $key => $filterMap) {
			if (null === ($value = self::getRequestParameterValue($request, $key))) {
				continue;
			}
			
			$criteria[$filterMap['field'] ?: $key] = FilterUtil::castTo($value, $filterMap['type']);
		}
		
		return $criteria;
	}
	
	/**
	 * @param Request $request
	 * @param string $key
	 * @param mixed $defaultValue
	 * @return mixed
	 */
	public static function getRequestParameterValue(Request $request, $key, $defaultValue = null)
	{
		switch (true) {
			case $request->query->has($key):
				return $request->query->get($key);
			case $request->attributes->has($key):
				return $request->attributes->get($key);
			default:
				return $defaultValue;
		}
	}
	
	/**
	 * @param Request $request
	 * @param string $key
	 * @param string $delimiter
	 * @param mixed $defaultValue
	 * @return mixed|[]
	 */
	public static function parseQueryToArray(Request $request, $key, $delimiter = null, $defaultValue = null)
	{
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
	public static function sanitizeQuery($query)
	{
		return trim(rawurldecode($query));
	}
}
