<?php

namespace Oka\PaginationBundle\Pagination\FilterExpression;

use Doctrine\ODM\MongoDB\Query\Builder;
use Oka\PaginationBundle\Pagination\Filter;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
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

    public function evaluate(object $queryBuilder, string $field, string $value, string $castType, ?string $propertyType = null, int &$boundCounter = 1): void
    {
        $evaluated = false;

        /** @var FilterExpressionInterface $filterExpression */
        foreach ($this->filterExpressions as $filterExpression) {
            if (false === $filterExpression->supports($queryBuilder, $value)) {
                continue;
            }

            $result = $filterExpression->evaluate($queryBuilder, $field, $value, $castType, $boundCounter);

            if ($queryBuilder instanceof Builder) {
                $queryBuilder->addAnd($result->getExpr());
            } else {
                $queryBuilder->andWhere($result->getExpr());

                foreach ($result->getParameters() as $name => $value) {
                    $queryBuilder->setParameter($name, $value, $propertyType);
                }
                ++$boundCounter;
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
            $queryBuilder->andWhere($queryBuilder->expr()->eq($field, '?'.$boundCounter));
            $queryBuilder->setParameter($boundCounter, $value, $propertyType);
            ++$boundCounter;
        }
    }
}
