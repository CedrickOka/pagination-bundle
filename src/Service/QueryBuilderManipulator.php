<?php
namespace Oka\PaginationBundle\Service;

use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Oka\PaginationBundle\Converter\QueryExprConverterInterface;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * @deprecated Use instead QueryBuilderHandler class.
 */
class QueryBuilderManipulator extends QueryBuilderHandler {}
