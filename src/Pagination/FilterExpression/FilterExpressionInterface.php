<?php
namespace Oka\PaginationBundle\Pagination\FilterExpression;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
interface FilterExpressionInterface
{
	/**
	 * checks if the filter value can be evaluated as this expression
	 */
	public function supports(object $queryBuilder, string $value) :bool;
	
	/**
	 * Evaluate filter value expression
	 * 
	 * @param object $queryBuilder The query builder
	 */
	public function evaluate(object $queryBuilder, string $field, string $value, string $castType, int &$boundCounter = 1) :EvaluationResult;
}
