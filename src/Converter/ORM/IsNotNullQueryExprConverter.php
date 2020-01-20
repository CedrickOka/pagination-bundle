<?php
namespace Oka\PaginationBundle\Converter\ORM;

use Doctrine\ORM\QueryBuilder;
use Oka\PaginationBundle\Converter\AbstractQueryExprConverter;
use Oka\PaginationBundle\Exception\BadQueryExprException;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class IsNotNullQueryExprConverter extends AbstractQueryExprConverter
{
	const PATTERN = '#^isNotNull\(\)$#i';
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\PaginationBundle\Converter\QueryExprConverterInterface::apply()
	 */
	public function apply(object $queryBuilder, string $alias, string $field, string $exprValue, string $namedParameter = null, &$value = null)
	{
		if (!preg_match(self::PATTERN, $exprValue)) {
			throw new BadQueryExprException(sprintf('The query expression converter "isNotNull" does not support the following pattern "%s".', $exprValue));
		}
		$value = null;
		
		return (new \Doctrine\ORM\Query\Expr())->isNotNull($alias.'.'.$field);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\PaginationBundle\Converter\AbstractQueryExprConverter::supports()
	 */
	public function supports(object $queryBuilder, string $exprValue) :bool
	{
	    return $queryBuilder instanceof QueryBuilder && preg_match(self::PATTERN, $exprValue);
	}
}
