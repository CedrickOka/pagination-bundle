<?php
namespace Oka\PaginationBundle\Converter\Mongodb;

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
	 * {@inheritdoc}
	 * @see \Oka\PaginationBundle\Converter\QueryExprConverter::apply()
	 */
	public function apply($dbDriver, $alias, $field, $exprValue, $namedParameter = null, &$value = null)
	{
		$matches = [];
		
		if (!preg_match(self::PATTERN, $exprValue, $matches)) {
			throw new BadQueryExprException(sprintf('The query expression converter "range" does not support the following pattern "%s".', $exprValue));
		}
		
		switch (true) {
			case $matches[2] && !$matches[3]:
				return $this->createGreaterExpr($field, $matches[1], trim($matches[2]));
				
			case !$matches[2] && $matches[3]:
				return $this->createLessExpr($field, $matches[4], trim($matches[3]));
				
			case $matches[2] && $matches[3]:
				return (new \Doctrine\MongoDB\Query\Expr())->addAnd(
						$this->createGreaterExpr($field, $matches[1], trim($matches[2])), 
						$this->createLessExpr($field, $matches[4], trim($matches[3]))
					);
				
			default:
				throw new BadQueryExprException('the range query expression converter requires left or right value of range');
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\PaginationBundle\Converter\AbstractQueryExprConverter::supports()
	 */
	public function supports($dbDriver, $exprValue)
	{
		return $dbDriver === 'mongodb' && preg_match(self::PATTERN, $exprValue);
	}
	
	private function createGreaterExpr($leftExpr, $operator, $rightExpr)
	{
		if ($operator === ']') {
			return (new \Doctrine\MongoDB\Query\Expr())->field($leftExpr)->gt($rightExpr);
		} else {
			return (new \Doctrine\MongoDB\Query\Expr())->field($leftExpr)->gte($rightExpr);			
		}
	}
	
	private function createLessExpr($leftExpr, $operator, $rightExpr)
	{
		if ($operator === '[') {
			return (new \Doctrine\MongoDB\Query\Expr())->field($leftExpr)->lt($rightExpr);
		} else {
			return (new \Doctrine\MongoDB\Query\Expr())->field($leftExpr)->lte($rightExpr);
		}
	}
}
