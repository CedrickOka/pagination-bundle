<?php

declare(strict_types=1);

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
     * @param object $queryBuilder
     * @param string $field
     * @param $value
     * @param string $castType
     * @param int $boundCounter
     * @return EvaluationResult
     */
    public function evaluate(object $queryBuilder, string $field, $value, string $castType, int &$boundCounter = 1): EvaluationResult
    {
        $matches = [];

        if (!preg_match(self::getExpressionPattern(), $value, $matches)) {
            throw new BadFilterExpressionException(sprintf('The orm rLike filter expression does not support the following value "%s".', $value));
        }

        $pattern = trim($matches['pattern']);

        // Security: Limit regex pattern length to prevent ReDoS
        if (strlen($pattern) > 100) {
            throw new BadFilterExpressionException('Regex pattern too long (max 100 characters)');
        }

        // Security: Validate pattern doesn't contain potentially dangerous constructs
        if (preg_match('/\(\?<!|\(\?=|\(\?!|\{\d+,\}/', $pattern)) {
            throw new BadFilterExpressionException('Complex regex patterns are not allowed');
        }

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
