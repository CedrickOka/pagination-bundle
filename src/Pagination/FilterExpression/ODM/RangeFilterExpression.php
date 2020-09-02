<?php
namespace Oka\PaginationBundle\Pagination\FilterExpression\ODM;

use Doctrine\ODM\MongoDB\Query\Builder;
use Oka\PaginationBundle\Exception\BadFilterExpressionException;
use Oka\PaginationBundle\Pagination\Filter;
use Oka\PaginationBundle\Pagination\FilterExpression\EvaluationResult;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class RangeFilterExpression extends AbstractODMFilterExpression
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
				return new EvaluationResult($this->createGreaterExpr($queryBuilder, $field, $matches['leftOperator'], Filter::castTo($start, $castType)));
				
			case !$start && $end:
				return new EvaluationResult($this->createLessExpr($queryBuilder, $field, $matches['rightOperator'], Filter::castTo($end, $castType)));
				
			case $start && $end:
				$queryBuilder instanceof Builder;
				return new EvaluationResult($queryBuilder->expr()->addAnd(
					$this->createGreaterExpr($queryBuilder, $field, $matches['leftOperator'], Filter::castTo($start, $castType)), 
					$this->createLessExpr($queryBuilder, $field, $matches['rightOperator'], Filter::castTo($end, $castType))
				));
				
			default:
				throw new BadFilterExpressionException('The range filter expression requires left or right value.');
		}
	}
	
	protected function createGreaterExpr(Builder $queryBuilder, string $field, string $leftOperator, $start)
	{
		return ']' === $leftOperator ? 
			$queryBuilder->expr()->field($field)->gt($start) : 
			$queryBuilder->expr()->field($field)->gte($start);
	}
	
	protected function createLessExpr(Builder $queryBuilder, string $field, string $rightOperator, $end)
	{
		return '[' === $rightOperator ? 
		    $queryBuilder->expr()->field($field)->lt($end) : 
		    $queryBuilder->expr()->field($field)->lte($end);
	}
	
	protected static function getExpressionPattern() :string
	{
		return '#^range(?<leftOperator>\[|\])(?<start>.*),(?<end>.*)(?<rightOperator>\[|\])$#i';
	}
}
