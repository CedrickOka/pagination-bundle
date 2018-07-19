<?php
namespace Oka\PaginationBundle\Converter\ORM;

use Oka\PaginationBundle\Converter\AbstractQueryExprConverter;
use Oka\PaginationBundle\Exception\BadQueryExprException;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class IsNullQueryExprConverter extends AbstractQueryExprConverter
{
	const PATTERN = '#^isNull\(\)$#i';
	
	/**
	 * {@inheritdoc}
	 * @see \Oka\PaginationBundle\Converter\QueryExprConverter::apply()
	 */
	public function apply($dbDriver, $alias, $field, $exprValue, $namedParameter = null, &$value = null)
	{
		if (!preg_match(self::PATTERN, $exprValue)) {
			throw new BadQueryExprException(sprintf('The query expression converter "isNull" does not support the following pattern "%s".', $exprValue));
		}
		$value = null;
		
		return (new \Doctrine\ORM\Query\Expr())->isNull($alias.'.'.$field);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\PaginationBundle\Converter\AbstractQueryExprConverter::supports()
	 */
	public function supports($dbDriver, $exprValue)
	{
	    return $dbDriver === 'orm' && preg_match(self::PATTERN, $exprValue);
	}
}
