<?php
namespace Oka\PaginationBundle\Converter\DBAL;

use Doctrine\ORM\QueryBuilder;
use Oka\PaginationBundle\Converter\AbstractQueryExprConverter;
use Oka\PaginationBundle\Exception\BadQueryExprException;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class EqualQueryExprConverter extends AbstractQueryExprConverter
{
	const PATTERN = '#^eq\((.+)\)$#i';
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\PaginationBundle\Converter\QueryExprConverterInterface::apply()
	 */
	public function apply(object $queryBuilder, string $alias, string $field, string $exprValue, string $namedParameter = null, &$value = null)
	{
		$matches = [];
		
		if (!preg_match(self::PATTERN, $exprValue, $matches)) {
			throw new BadQueryExprException(sprintf('The query expression converter "eq" does not support the following pattern "%s".', $exprValue));
		}
		
		$value = $matches[1];
		
		return $queryBuilder instanceof QueryBuilder ? 
    		$queryBuilder->expr()->eq($alias.'.'.$field, $namedParameter ?: ':'.$field) : 
    		$queryBuilder->expr()->field($field)->equals($value);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\PaginationBundle\Converter\AbstractQueryExprConverter::supports()
	 */
	public function supports(object $queryBuilder, string $exprValue) :bool
	{
		return (bool) preg_match(self::PATTERN, $exprValue);
	}
}
