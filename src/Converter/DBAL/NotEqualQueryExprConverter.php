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
		
		return $queryBuilder instanceof QueryBuilder ?
    		$queryBuilder->expr()->neq($alias.'.'.$field, $namedParameter ?: ':'.$field) :
    		$queryBuilder->expr()->field($field)->notEqual($value);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\PaginationBundle\Converter\AbstractQueryExprConverter::supports()
	 */
	public function supports(object $queryBuilder, string $exprValue) :bool
	{
		return (boolean) preg_match(self::PATTERN, $exprValue);
	}
}
