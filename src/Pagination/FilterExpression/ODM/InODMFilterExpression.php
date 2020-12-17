<?php
namespace Oka\PaginationBundle\Pagination\FilterExpression\ODM;

use Oka\PaginationBundle\Exception\BadFilterExpressionException;
use Oka\PaginationBundle\Pagination\Filter;
use Oka\PaginationBundle\Pagination\FilterExpression\EvaluationResult;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class InODMFilterExpression extends AbstractODMFilterExpression
{
	public function evaluate(object $queryBuilder, string $field, string $value, string $castType, int &$boundCounter = 1) :EvaluationResult
	{
		$matches = [];
		
		if (!preg_match(self::getExpressionPattern(), $value, $matches)) {
			throw new BadFilterExpressionException(sprintf('The in filter expression does not support the following pattern "%s".', $value));
		}
		
		$values = [];
		
		foreach (explode(',', $matches[1]) as $value) {
			$values[] = Filter::castTo($value, $castType);
		}
		
		/** @var \Doctrine\ODM\MongoDB\Query\Builder $queryBuilder */
		return new EvaluationResult($queryBuilder->expr()->field($field)->in($values));
	}
	
	protected static function getExpressionPattern() :string
	{
		return '#^in\((.+)\)$#i';
	}
}
