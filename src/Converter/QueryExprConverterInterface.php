<?php
namespace Oka\PaginationBundle\Converter;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
interface QueryExprConverterInterface
{
	/**
	 * @param string $dbDriver The database driver name selected.
	 * @param string $field The entity field name
	 * @param string $exprValue The URL query expression value
	 * @param string $namedParameter The paramerter named of the prepared query
	 * @param string $value The value found in expression value
	 * 
	 * @return \Doctrine\ORM\Query\Expr|\Doctrine\MongoDB\Query\Expr
	 */
	public function apply($dbDriver, $alias, $field, $exprValue, $namedParameter = null, &$value = null);
	
	/**
	 * Checks if the expression value is supported.
	 * 
	 * @param string $dbDriver The database driver name selected.
	 * @param string $exprValue The URL query expression value
	 * 
	 * @return bool True if the expression value is supported, else false
	 */
	public function supports($dbDriver, $exprValue);
}
