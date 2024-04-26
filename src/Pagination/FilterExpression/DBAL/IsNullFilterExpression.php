<?php

namespace Oka\PaginationBundle\Pagination\FilterExpression\DBAL;

use Oka\PaginationBundle\Exception\BadFilterExpressionException;
use Oka\PaginationBundle\Pagination\FilterExpression\AbstractFilterExpression;
use Oka\PaginationBundle\Pagination\FilterExpression\EvaluationResult;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class IsNullFilterExpression extends AbstractFilterExpression
{
    public function evaluate(object $queryBuilder, string $field, string $value, string $castType, int &$boundCounter = 1): EvaluationResult
    {
        if (!preg_match(self::getExpressionPattern(), $value)) {
            throw new BadFilterExpressionException(sprintf('The isNull filter expression does not support the following pattern "%s".', $value));
        }

        switch (true) {
            case $queryBuilder instanceof \Doctrine\ORM\QueryBuilder:
                return new EvaluationResult($queryBuilder->expr()->isNull($field));

            case $queryBuilder instanceof \Doctrine\ODM\MongoDB\Query\Builder:
                return new EvaluationResult($queryBuilder->expr()->field($field)->equals(null));
        }
    }

    protected static function getExpressionPattern(): string
    {
        return '#^isNull\(\)$#i';
    }
}
