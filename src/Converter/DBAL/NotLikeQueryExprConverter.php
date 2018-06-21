<?php
namespace Oka\PaginationBundle\Converter\DBAL;

use Oka\PaginationBundle\Converter\AbstractQueryExprConverter;
use Oka\PaginationBundle\Exception\BadQueryExprException;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class NotLikeQueryExprConverter extends AbstractQueryExprConverter
{
	const PATTERN = '#^notlike\((.+)\)$#i';
	
	/**
	 * {@inheritdoc}
	 * @see \Oka\PaginationBundle\Converter\QueryExprConverter::apply()
	 */
	public function apply($dbDriver, $alias, $field, $exprValue, $namedParameter = null, &$value = null)
	{
		$matches = [];
		preg_match(self::PATTERN, $exprValue, $matches);
		
		if (!preg_match(self::PATTERN, $exprValue, $matches)) {
			throw new BadQueryExprException(sprintf('The not like query expression converter does not support the following pattern "%s".', $exprValue));
		}
		
		$value = $matches[1];
		
		return $dbDriver === 'orm' ? 
				(new \Doctrine\ORM\Query\Expr())->notLike($alias.'.'.$field, $namedParameter ?: ':'.$field) : 
				(new \Doctrine\MongoDB\Query\Expr())->not((new \Doctrine\MongoDB\Query\Expr())->field($field)->text($value));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\PaginationBundle\Converter\AbstractQueryExprConverter::supports()
	 */
	public function supports($dbDriver, $exprValue)
	{
		return (boolean) preg_match(self::PATTERN, $exprValue);
	}
}
