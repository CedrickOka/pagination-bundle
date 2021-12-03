<?php

namespace Oka\PaginationBundle\Pagination\FilterExpression;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
abstract class AbstractFilterExpression implements FilterExpressionInterface
{
    public function supports(object $queryBuilder, string $value): bool
    {
        return (bool) preg_match(static::getExpressionPattern(), $value);
    }

    abstract protected static function getExpressionPattern(): string;
}
