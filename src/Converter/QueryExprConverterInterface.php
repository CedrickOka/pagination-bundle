<?php
namespace Oka\PaginationBundle\Converter;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
interface QueryExprConverterInterface
{
    public function apply(object $queryBuilder, string $alias, string $field, string $exprValue, string $namedParameter = null, &$value = null);
	
    public function supports(object $queryBuilder, string $exprValue) :bool;
}
