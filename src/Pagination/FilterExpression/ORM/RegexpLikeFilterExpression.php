<?php

namespace Oka\PaginationBundle\Pagination\FilterExpression\ORM;

use Doctrine\ORM\Query\Expr\Func;
use Oka\PaginationBundle\Exception\BadFilterExpressionException;
use Oka\PaginationBundle\Pagination\FilterExpression\EvaluationResult;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class RegexpLikeFilterExpression extends AbstractORMFilterExpression
{
    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     */
    public function evaluate(object $queryBuilder, string $field, $value, string $castType, int &$boundCounter = 1): EvaluationResult
    {
        $matches = [];

        if (!preg_match(self::getExpressionPattern(), $value, $matches)) {
            throw new BadFilterExpressionException(sprintf('The orm rLike filter expression does not support the following value "%s".', $value));
        }

        $pattern = trim($matches['pattern']);

        if (!isset($matches['matchType'])) {
            return new EvaluationResult(
                $queryBuilder->expr()->eq(
                    new Func(
                        'REGEXP_LIKE',
                        [$field, $queryBuilder->expr()->literal($pattern)->__toString()]
                    ),
                    1
                ),
                []
            );
        } else {
            return new EvaluationResult(
                $queryBuilder->expr()->eq(
                    new Func(
                        'REGEXP_LIKE',
                        [
                            $field,
                            $queryBuilder->expr()->literal($pattern)->__toString(),
                            $queryBuilder->expr()->literal(trim($matches['matchType']))->__toString(),
                        ]
                    ),
                    1
                ),
                []
            );
        }
    }

    protected static function getExpressionPattern(): string
    {
        return '#^rLike\((?<pattern>.+),(?<matchType>[cimnu]{1,5})?\)$#i';
    }
}
