<?php

namespace Oka\PaginationBundle\Pagination\FilterExpression\ORM;

use Doctrine\ORM\QueryBuilder;
use Oka\PaginationBundle\Pagination\FilterExpression\AbstractFilterExpression;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
abstract class AbstractORMFilterExpression extends AbstractFilterExpression
{
    public function supports(object $queryBuilder, string $value): bool
    {
        return parent::supports($queryBuilder, $value) && $queryBuilder instanceof QueryBuilder;
    }
}
