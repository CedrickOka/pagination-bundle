<?php
namespace Oka\PaginationBundle\Pagination\FilterExpression\DBAL;

use Oka\PaginationBundle\Exception\BadFilterExpressionException;
use Oka\PaginationBundle\Pagination\FilterExpression\AbstractFilterExpression;
use Oka\PaginationBundle\Pagination\FilterExpression\EvaluationResult;


/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class IsNotNullFilterExpression extends AbstractFilterExpression
{
	public function evaluate(object $queryBuilder, string $field, string $value, string $castType) :EvaluationResult
	{
		if (!preg_match(self::getExpressionPattern(), $value)) {
			throw new BadFilterExpressionException(sprintf('The isNotNull filter expression does not support the following pattern "%s".', $value));
		}
		
		switch (true) {
			case $queryBuilder instanceof \Doctrine\ORM\QueryBuilder:
				return new EvaluationResult($queryBuilder->expr()->isNotNull($field));
				
			case $queryBuilder instanceof \Doctrine\ODM\MongoDB\Query\Builder:
				return new EvaluationResult($queryBuilder->expr()->field($field)->notEqual(null));
		}
	}
	
	protected static function getExpressionPattern() :string
	{
		return '#^isNotNull\(\)$#i';
	}
}
