<?php
namespace Oka\PaginationBundle\Pagination\FilterExpression\ODM;

use Doctrine\ORM\QueryBuilder;
use Oka\PaginationBundle\Pagination\FilterExpression\AbstractFilterExpression;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
abstract class AbstractODMFilterExpression extends AbstractFilterExpression
{
	public function supports(object $queryBuilder, string $value) :bool
	{
		return parent::supports($queryBuilder, $value) && $queryBuilder instanceof QueryBuilder;
	}
}
