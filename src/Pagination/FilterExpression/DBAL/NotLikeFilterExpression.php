<?php

namespace Oka\PaginationBundle\Pagination\FilterExpression\DBAL;

use Oka\PaginationBundle\Exception\BadFilterExpressionException;
use Oka\PaginationBundle\Pagination\Filter;
use Oka\PaginationBundle\Pagination\FilterExpression\AbstractFilterExpression;
use Oka\PaginationBundle\Pagination\FilterExpression\EvaluationResult;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class NotLikeFilterExpression extends AbstractFilterExpression
{
    public function evaluate(object $queryBuilder, string $field, string $value, string $castType, int &$boundCounter = 1): EvaluationResult
    {
        $matches = [];

        if (!preg_match(self::getExpressionPattern(), $value, $matches)) {
            throw new BadFilterExpressionException(sprintf('The like filter expression does not support the following pattern "%s".', $value));
        }

        $value = Filter::castTo($matches[1], $castType);

        switch (true) {
            case $queryBuilder instanceof \Doctrine\ORM\QueryBuilder:
                return new EvaluationResult($queryBuilder->expr()->notLike($field, '?'.$boundCounter), [$boundCounter => $value]);

            case $queryBuilder instanceof \Doctrine\ODM\MongoDB\Query\Builder:
                return new EvaluationResult($queryBuilder->expr()->not($queryBuilder->expr()->field($field)->text($value)));
        }
    }

    protected static function getExpressionPattern(): string
    {
        return '#^notLike\((.+)\)$#i';
    }
}
