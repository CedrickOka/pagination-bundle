<?php
namespace Oka\PaginationBundle\Converter\DBAL;

use Oka\PaginationBundle\Converter\AbstractQueryExprConverter;
use Oka\PaginationBundle\Exception\BadQueryExprException;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class IsNullQueryExprConverter extends AbstractQueryExprConverter
{
	const PATTERN = '#^isNull\(\)$#i';
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\PaginationBundle\Converter\QueryExprConverterInterface::apply()
	 */
	public function apply(object $queryBuilder, string $alias, string $field, string $exprValue, string $namedParameter = null, &$value = null)
	{
		if (!preg_match(self::PATTERN, $exprValue)) {
			throw new BadQueryExprException(sprintf('The query expression converter "isNull" does not support the following pattern "%s".', $exprValue));
		}
		
		$value = null;
		
		switch (true) {
		    case $queryBuilder instanceof \Doctrine\ORM\QueryBuilder:
		        $queryBuilder->expr()->isNull($alias.'.'.$field);
		        break;
		        
		    case $queryBuilder instanceof \Doctrine\ODM\MongoDB\Query\Builder:
		        $queryBuilder->expr()->field($field)->equal($value);
		        break;
		        
		    default:
		        break;
		}
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
