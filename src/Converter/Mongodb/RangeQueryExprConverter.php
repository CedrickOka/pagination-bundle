<?php
namespace Oka\PaginationBundle\Converter\Mongodb;

use Doctrine\ODM\MongoDB\Query\Builder;
use Oka\PaginationBundle\Converter\AbstractQueryExprConverter;
use Oka\PaginationBundle\Exception\BadQueryExprException;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class RangeQueryExprConverter extends AbstractQueryExprConverter
{
	const PATTERN = '#^range(\[|\])(.*),(.*)(\[|\])$#i';
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\PaginationBundle\Converter\QueryExprConverterInterface::apply()
	 */
	public function apply(object $queryBuilder, string $alias, string $field, $exprValue, string $namedParameter = null, &$value = null)
	{
		$matches = [];
		
		if (!preg_match(self::PATTERN, $exprValue, $matches)) {
			throw new BadQueryExprException(sprintf('The query expression converter "range" does not support the following pattern "%s".', $exprValue));
		}
		
		switch (true) {
			case $matches[2] && !$matches[3]:
			    return $this->createGreaterExpr($queryBuilder, $field, $matches[1], trim($matches[2]));
				
			case !$matches[2] && $matches[3]:
			    return $this->createLessExpr($queryBuilder, $field, $matches[4], trim($matches[3]));
				
			case $matches[2] && $matches[3]:
			    return $queryBuilder->expr()->addAnd(
				    $this->createGreaterExpr($queryBuilder, $field, $matches[1], trim($matches[2])), 
				    $this->createLessExpr($queryBuilder, $field, $matches[4], trim($matches[3]))
				);
				
			default:
				throw new BadQueryExprException('the range query expression converter requires left or right value of range');
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\PaginationBundle\Converter\AbstractQueryExprConverter::supports()
	 */
	public function supports(object $queryBuilder, $exprValue) :bool
	{
	    return $queryBuilder instanceof Builder && preg_match(self::PATTERN, $exprValue);
	}
	
	private function createGreaterExpr(Builder $queryBuilder, $leftExpr, $operator, $rightExpr)
	{
	    return ']' === $operator ? 
    	    $queryBuilder->expr()->field($leftExpr)->gt($rightExpr) : 
    	    $queryBuilder->expr()->field($leftExpr)->gte($rightExpr);
	}
	
	private function createLessExpr(Builder $queryBuilder, $leftExpr, $operator, $rightExpr)
	{
	    return '[' === $operator ? 
    	    $queryBuilder->expr()->field($leftExpr)->lt($rightExpr) : 
    	    $queryBuilder->expr()->field($leftExpr)->lte($rightExpr);
	}
}
