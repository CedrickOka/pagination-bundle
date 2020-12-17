<?php
namespace Oka\PaginationBundle\Pagination\FilterExpression\ORM;

use Oka\PaginationBundle\Exception\BadFilterExpressionException;
use Oka\PaginationBundle\Pagination\Filter;
use Oka\PaginationBundle\Pagination\FilterExpression\EvaluationResult;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class InORMFilterExpression extends AbstractORMFilterExpression
{
	public function evaluate(object $queryBuilder, string $field, string $value, string $castType, int &$boundCounter = 1) :EvaluationResult
	{
		$matches = [];
		
		if (!preg_match(self::getExpressionPattern(), $value, $matches)) {
			throw new BadFilterExpressionException(sprintf('The in filter expression does not support the following pattern "%s".', $value));
		}
		
		$values = [];
		$parameters = [];
		
		foreach (implode(',', $matches[1]) as $value) {
			$parameters[$boundCounter] = Filter::castTo($value, $castType);
			$values[] = '?' . $boundCounter;
			++$boundCounter;
		}
		--$boundCounter;
		
		/** @var \Doctrine\ORM\QueryBuilder $queryBuilder */
		$value = Filter::castTo($matches[1], $castType);
		
		return new EvaluationResult($queryBuilder->expr()->in($field, $values), $parameters);
	}
	
	protected static function getExpressionPattern() :string
	{
		return '#^in\((.+)\)$#i';
	}
}
