<?php
namespace Oka\PaginationBundle\Pagination\FilterExpression;

use Doctrine\ODM\MongoDB\Query\Builder;
use Oka\PaginationBundle\Pagination\Filter;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class FilterExpressionHandler
{
	private $filterExpressions;
	
	public function __construct(iterable $filterExpressions = [])
	{
		$this->filterExpressions = $filterExpressions;
	}
	
	public function addFilterExpression(FilterExpressionInterface $filterExpression)
	{
		$this->filterExpressions[] = $filterExpression;
	}
	
	public function evaluate(object $queryBuilder, string $field, string $value, string $castType, string $propertyType = null) :void
	{
		$evaluated = false;
		$boundCounter = 0;
		
		/** @var \Oka\PaginationBundle\Pagination\FilterExpression\FilterExpressionInterface $filterExpression */
		foreach ($this->filterExpressions as $filterExpression) {
			if (false === $filterExpression->supports($queryBuilder, $value)) {
				continue;
			}
			
			$result = $filterExpression->evaluate($queryBuilder, $field, $value, $castType);
			
			if ($queryBuilder instanceof Builder) {
				$queryBuilder->addAnd($result->getExpr());
			} else {
				$queryBuilder->andWhere($result->getExpr());
				
				foreach ($result->getValues() as $value) {
					$queryBuilder->setParameter(++$boundCounter, $value, $propertyType);
				}
			}
			
			$evaluated = true;
			break;
		}
		
		if (true === $evaluated) {
			return;
		}
		
		$value = Filter::castTo($value, $castType);
		
		if ($queryBuilder instanceof Builder) {
			$queryBuilder->field($field)->equals($value);
		} else {
			$queryBuilder->andWhere($queryBuilder->expr()->eq($field, '?'));
			$queryBuilder->setParameter(++$boundCounter, $value, $propertyType);
		}
	}
}
