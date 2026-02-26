<?php

declare(strict_types=1);

namespace Oka\PaginationBundle\Pagination\FilterExpression\ORM;

use Doctrine\ORM\QueryBuilder;
use Oka\PaginationBundle\Exception\BadFilterExpressionException;
use Oka\PaginationBundle\Pagination\Filter;
use Oka\PaginationBundle\Pagination\FilterExpression\EvaluationResult;
use Oka\PaginationBundle\Pagination\FilterExpression\RangeFilterExpressionTrait;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class RangeORMFilterExpression extends AbstractORMFilterExpression
{
    use RangeFilterExpressionTrait;

    public function evaluate(object $queryBuilder, string $field, $value, string $castType, int &$boundCounter = 1): EvaluationResult
    {
        $matches = [];

        if (!preg_match(self::getExpressionPattern(), $value, $matches)) {
            throw new BadFilterExpressionException(sprintf('The orm range filter expression does not support the following value "%s".', $value));
        }

        $start = trim($matches['start']);
        $end = trim($matches['end']);

        // Security: Validate range values length to prevent abuse
        $maxValueLength = 100;
        if (strlen($start) > $maxValueLength || strlen($end) > $maxValueLength) {
            throw new BadFilterExpressionException(sprintf('Range value too long (max %d characters)', $maxValueLength));
        }

        switch (true) {
            case $start && !$end:
                return new EvaluationResult(
                    $this->createGreaterExpr($queryBuilder, $field, $matches['leftOperator'], '?'.$boundCounter),
                    [$boundCounter => Filter::castTo($start, $castType)]
                );

            case !$start && $end:
                return new EvaluationResult(
                    $this->createLessExpr($queryBuilder, $field, $matches['rightOperator'], '?'.$boundCounter),
                    [$boundCounter => Filter::castTo($end, $castType)]
                );

            case $start && $end:
                $startBoundCounter = $boundCounter;
                $endBoundCounter = ++$boundCounter;

                return new EvaluationResult(
                    $queryBuilder->expr()->andX(
                        $this->createGreaterExpr($queryBuilder, $field, $matches['leftOperator'], '?'.$startBoundCounter),
                        $this->createLessExpr($queryBuilder, $field, $matches['rightOperator'], '?'.$endBoundCounter)
                    ),
                    [$startBoundCounter => Filter::castTo($start, $castType), $endBoundCounter => Filter::castTo($end, $castType)]
                );

            default:
                throw new BadFilterExpressionException('The range filter expression requires left or right value.');
        }
    }

    protected function createGreaterExpr(QueryBuilder $queryBuilder, string $field, string $leftOperator, string $placeholder): \Doctrine\ORM\Query\Expr\Comparison
    {
        return ']' === $leftOperator ?
            $queryBuilder->expr()->gt($field, $placeholder) :
            $queryBuilder->expr()->gte($field, $placeholder);
    }

    protected function createLessExpr(QueryBuilder $queryBuilder, $field, $rightOperator, string $placeholder): \Doctrine\ORM\Query\Expr\Comparison
    {
        return '[' === $rightOperator ?
            $queryBuilder->expr()->lt($field, $placeholder) :
            $queryBuilder->expr()->lte($field, $placeholder);
    }
}
