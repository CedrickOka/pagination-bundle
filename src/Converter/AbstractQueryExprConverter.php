<?php
namespace Oka\PaginationBundle\Converter;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
abstract class AbstractQueryExprConverter implements QueryExprConverterInterface
{
	/**
     * {@inheritDoc}
     * @see \Oka\PaginationBundle\Converter\QueryExprConverterInterface::supports()
     */
	public function supports(object $queryBuilder, $exprValue) :bool
	{
		return false;
	}
}
