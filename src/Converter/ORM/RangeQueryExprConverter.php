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
class RangeQueryExprConverter extends AbstractQueryExprConverter
{
	const PATTERN = '#^range(\[|\])(.*),(.*)(\[|\])$#i';
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\PaginationBundle\Converter\QueryExprConverterInterface::apply()
	 */
	public function apply(object $queryBuilder, string $alias, string $field, string $exprValue, string $namedParameter = null, &$value = null) :?object
	{
		$value = [];
		$matches = [];
		$leftExpr = $alias.'.'.$field;
		
		if (!preg_match(self::PATTERN, $exprValue, $matches)) {
			throw new BadQueryExprException(sprintf('The query expression converter "range" does not support the following pattern "%s".', $exprValue));
		}
		
		switch (true) {
			case $matches[2] && !$matches[3]:
				$rightExpr = $namedParameter ?: ':'.$field;
				$value[$rightExpr] = trim($matches[2]);
				return $this->createGreaterThanExpr($leftExpr, $matches[1], $rightExpr);
				
			case !$matches[2] && $matches[3]:
				$rightExpr = $namedParameter ?: ':'.$field;
				$value[$rightExpr] = trim($matches[3]);
				return $this->createLessThanExpr($leftExpr, $matches[4], $rightExpr);
				
			case $matches[2] && $matches[3]:
				$rightExpr1 = ($namedParameter ?: ':'.$field) . '1';
				$rightExpr2 = ($namedParameter ?: ':'.$field) . '2';
				$value[$rightExpr1] = trim($matches[2]);
				$value[$rightExpr2] = trim($matches[3]);
				return (new \Doctrine\ORM\Query\Expr())->andX(
					$this->createGreaterThanExpr($leftExpr, $matches[1], $rightExpr1), 
					$this->createLessThanExpr($leftExpr, $matches[4], $rightExpr2)
				);
				
			default:
				throw new BadQueryExprException('The range query expression converter requires left or right value of range');
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\PaginationBundle\Converter\AbstractQueryExprConverter::supports()
	 */
	public function supports(object $queryBuilder, string $exprValue) :bool
	{
	    return $queryBuilder instanceof QueryBuilder && preg_match(self::PATTERN, $exprValue);
	}
	
	private function createGreaterThanExpr($leftExpr, $operator, $rightExpr)
	{
	    return ']' === $operator ? 
    	    (new \Doctrine\ORM\Query\Expr())->gt($leftExpr, $rightExpr) : 
    	    (new \Doctrine\ORM\Query\Expr())->gte($leftExpr, $rightExpr);
	}
	
	private function createLessThanExpr($leftExpr, $operator, $rightExpr)
	{
	    return '[' === $operator ? 
	       (new \Doctrine\ORM\Query\Expr())->lt($leftExpr, $rightExpr) : 
	       (new \Doctrine\ORM\Query\Expr())->lte($leftExpr, $rightExpr);
	}
}
