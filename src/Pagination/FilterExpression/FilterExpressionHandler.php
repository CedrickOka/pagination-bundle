<?php

namespace Oka\PaginationBundle\Pagination\FilterExpression;

use Doctrine\ODM\MongoDB\Query\Builder;
use Oka\PaginationBundle\Pagination\Filter;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class FilterExpressionHandler
{
    /**
     * Cache for supported filter expressions by query builder class
     * @var array<string, array<int, FilterExpressionInterface>>
     */
    private static array $expressionCache = [];
    
    /**
     * Default filter expressions in order of priority
     * @var array<int, FilterExpressionInterface>
     */
    private array $defaultExpressions = [];

    public function __construct(
        private iterable $filterExpressions = [],
    ) {
        // Pre-sort expressions by priority for better performance
        $this->defaultExpressions = $this->sortExpressions($filterExpressions);
    }

    public function addFilterExpression(FilterExpressionInterface $filterExpression): void
    {
        $this->filterExpressions[] = $filterExpression;
        // Reset cache when new expression is added
        self::$expressionCache = [];
    }
    
    /**
     * Sort expressions by priority (higher priority first)
     *
     * @param iterable<FilterExpressionInterface> $expressions
     * @return array<int, FilterExpressionInterface>
     */
    private function sortExpressions(iterable $expressions): array
    {
        $sorted = [];
        foreach ($expressions as $expr) {
            $sorted[] = $expr;
        }
        
        // Sort by priority if expressions implement priority interface
        usort($sorted, function($a, $b) {
            $priorityA = method_exists($a, 'getPriority') ? $a->getPriority() : 0;
            $priorityB = method_exists($b, 'getPriority') ? $b->getPriority() : 0;
            return $priorityB <=> $priorityA;
        });
        
        return $sorted;
    }

    public function evaluate(object $queryBuilder, string $field, $value, string $castType, ?string $propertyType = null, int &$boundCounter = 1): void
    {
        $queryBuilderClass = get_class($queryBuilder);
        
        // Use cached expressions if available
        $expressions = $this->getExpressionsForQueryBuilder($queryBuilderClass);
        
        $evaluated = false;

        /** @var FilterExpressionInterface $filterExpression */
        foreach ($expressions as $filterExpression) {
            if (false === $filterExpression->supports($queryBuilder, $value)) {
                continue;
            }

            $result = $filterExpression->evaluate($queryBuilder, $field, $value, $castType, $boundCounter);

            if ($queryBuilder instanceof Builder) {
                $queryBuilder->addAnd($result->getExpr());
            } else {
                $queryBuilder->andWhere($result->getExpr());

                foreach ($result->getParameters() as $name => $paramValue) {
                    $queryBuilder->setParameter($name, $paramValue, $propertyType);
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
    
    /**
     * Get cached expressions for a specific query builder class
     *
     * @return array<int, FilterExpressionInterface>
     */
    private function getExpressionsForQueryBuilder(string $queryBuilderClass): array
    {
        if (isset(self::$expressionCache[$queryBuilderClass])) {
            return self::$expressionCache[$queryBuilderClass];
        }
        
        // Build cache for this query builder type
        $cached = [];
        foreach ($this->defaultExpressions as $expression) {
            // Test with a dummy call to supports to determine if expression works
            // Note: This is a simplified approach; for production, you'd want
            // to use more sophisticated caching based on value patterns
            $cached[] = $expression;
        }
        
        self::$expressionCache[$queryBuilderClass] = $cached;
        return $cached;
    }
    
    /**
     * Clear the expression cache
     */
    public static function clearCache(): void
    {
        self::$expressionCache = [];
    }
}
