<?php
namespace Oka\PaginationBundle\Converter\DBAL;

use Oka\PaginationBundle\Converter\AbstractQueryExprConverter;
use Oka\PaginationBundle\Exception\BadQueryExprException;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class NotEqualQueryExprConverter extends AbstractQueryExprConverter
{
	const PATTERN = '#^neq\((.+)\)$#i';
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\PaginationBundle\Converter\QueryExprConverterInterface::apply()
	 */
	public function apply(object $queryBuilder, string $alias, string $field, string $exprValue, string $namedParameter = null, &$value = null)
	{
		$matches = [];
		
		if (!preg_match(self::PATTERN, $exprValue, $matches)) {
			throw new BadQueryExprException(sprintf('The query expression converter "neq" does not support the following pattern "%s".', $exprValue));
		}
		
		$value = $matches[1];
		
		switch (true) {
		    case $queryBuilder instanceof \Doctrine\ORM\QueryBuilder:
		        return $queryBuilder->expr()->neq($alias.'.'.$field, $namedParameter ?: ':'.$field);
		        
		    case $queryBuilder instanceof \Doctrine\ODM\MongoDB\Query\Builder:
		        return $queryBuilder->expr()->field($field)->notEqual($value);
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
