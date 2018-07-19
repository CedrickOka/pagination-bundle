<?php
namespace Oka\PaginationBundle\Converter\ORM;

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
	public function supports($dbDriver, $exprValue)
	{
		return $dbDriver === 'orm' && preg_match(self::PATTERN, $exprValue);
	}
	
	private function createGreaterThanExpr($leftExpr, $operator, $rightExpr)
	{
		if ($operator === ']') {
			return (new \Doctrine\ORM\Query\Expr())->gt($leftExpr, $rightExpr);
		} else {
			return (new \Doctrine\ORM\Query\Expr())->gte($leftExpr, $rightExpr);			
		}
	}
	
	private function createLessThanExpr($leftExpr, $operator, $rightExpr)
	{
		if ($operator === '[') {
			return (new \Doctrine\ORM\Query\Expr())->lt($leftExpr, $rightExpr);
		} else {
			return (new \Doctrine\ORM\Query\Expr())->lte($leftExpr, $rightExpr);
		}
	}
}
