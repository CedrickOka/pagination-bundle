<?php

declare(strict_types=1);

namespace Oka\PaginationBundle\Pagination\FilterExpression;

trait RangeFilterExpressionTrait
{
    protected static function getExpressionPattern(): string
    {
        return '#^range(?<leftOperator>\[|\])(?<start>.*),(?<end>.*)(?<rightOperator>\[|\])$#i';
    }
}
