<?php
namespace Oka\PaginationBundle\Pagination\FilterExpression\ORM;

use Doctrine\ORM\QueryBuilder;
use Oka\PaginationBundle\Exception\BadFilterExpressionException;
use Oka\PaginationBundle\Pagination\FilterExpression\EvaluationResult;
use Oka\PaginationBundle\Pagination\Filter;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class RangeFilterExpression extends AbstractORMFilterExpression
{
	public function evaluate(object $queryBuilder, string $field, string $value, string $castType) :EvaluationResult
	{
		$matches = [];
		
		if (!preg_match(self::getExpressionPattern(), $value, $matches)) {
			throw new BadFilterExpressionException(sprintf('The range filter expression does not support the following value "%s".', $value));
		}
		
		$start = trim($matches['start']);
		$end = trim($matches['end']);
		
		switch (true) {
			case $start && !$end:
				return new EvaluationResult($this->createGreaterExpr($queryBuilder, $field, $matches['leftOperator']), [Filter::castTo($start, $castType)]);
				
			case !$start && $end:
				return new EvaluationResult($this->createLessExpr($queryBuilder, $field, $matches['rightOperator']), [Filter::castTo($end, $castType)]);
				
			case $start && $end:
				return new EvaluationResult($queryBuilder->expr()->andX(
					$this->createGreaterExpr($queryBuilder, $field, $matches['leftOperator']),
					$this->createLessExpr($queryBuilder, $field, $matches['rightOperator'])
				), [Filter::castTo($start, $castType), Filter::castTo($end, $castType)]);
				
			default:
				throw new BadFilterExpressionException('The range filter expression requires left or right value.');
		}
	}
	
	protected function createGreaterExpr(QueryBuilder $queryBuilder, string $field, string $leftOperator)
	{
		return ']' === $leftOperator ? 
			$queryBuilder->expr()->gt($field, '?') : 
			$queryBuilder->expr()->gte($field, '?');
	}
	
	protected function createLessExpr(QueryBuilder $queryBuilder, $field, $rightOperator)
	{
		return '[' === $rightOperator ? 
			$queryBuilder->expr()->lt($field, '?') : 
			$queryBuilder->expr()->lte($field, '?');
	}
	
	protected static function getExpressionPattern() :string
	{
		return '#^range(?<leftOperator>\[|\])(?<start>.*),(?<end>.*)(?<rightOperator>\[|\])$#i';
	}
}
