<?php
namespace Oka\PaginationBundle\Service;

use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Oka\PaginationBundle\Converter\QueryExprConverterInterface;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class QueryBuilderManipulator
{
	/**
	 * @var array $mapConverters
	 */
	protected $mapConverters;

	/**
	 * Constructor.
	 *
	 * @param array $mapConverters
	 */
	public function __construct(array $mapConverters = [])
	{
		$this->mapConverters = $mapConverters;
	}

	/**
	 * Apply Expr() object in query builder with expression value
	 *
	 * @param QueryBuilder|Builder $qb
	 * @param string $alias
	 * @param string $field
	 * @param string $exprValue
	 * @param string $namedParameter
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 */
	public function applyExprFromString($qb, $alias, $field, $exprValue, $namedParameter = null)
	{
		if (!$qb instanceof QueryBuilder && !$qb instanceof Builder) {
			throw new \InvalidArgumentException(sprintf('Argument 1 passed to "%s" must be an instance of "%s" or "%s", "%s" given.', __METHOD__, '\Doctrine\ORM\QueryBuilder', '\Doctrine\ODM\MongoDB\Query\Builder', gettype($qb)));
		}

		$value = $exprValue;
		$namedParameter = $namedParameter ?: ':'.$field;
		$dbDriver = $qb instanceof QueryBuilder ? 'orm' : 'mongodb';

		foreach ($this->mapConverters as $mapConverter) {
			if (true === $this->supports($mapConverter, $dbDriver, $exprValue)) {
				$converter = new $mapConverter['class']();

				if (!$converter instanceof QueryExprConverterInterface) {
					throw new \RuntimeException(sprintf('Class "%s" must implemented interface "%s" for be used like query expression value converter.', $mapConverter['class'], '\Oka\PaginationBundle\Converter\QueryExprConverterInterface'));
				}

				$expr = $converter->apply($dbDriver, $alias, $field, $exprValue, $namedParameter, $value);
				break;
			}
		}
		
		if ($qb instanceof QueryBuilder) {
			if (!isset($expr)) {
				$expr = (new \Doctrine\ORM\Query\Expr())->eq($alias.'.'.$field, $namedParameter);
				$qb->setParameter($namedParameter, $exprValue);
			} else {
				if (is_array($value)) {
					foreach ($value as $key => $val) {
						$qb->setParameter($key, $val);
					}
				} else {
					$qb->setParameter($namedParameter, $value);
				}
			}
			
			$qb->andWhere($expr);
		} else {
			$qb->addAnd(isset($expr) ? $expr : (new \Doctrine\MongoDB\Query\Expr())->field($field)->equals($value));
		}
	}

	/**
	 * Apply Expr() object in query builder with array of criteria
	 *
	 * @param QueryBuilder|Builder $qb
	 * @param string $alias
	 * @param array $criteria
	 */
	public function applyExprFromArray($qb, $alias, array $criteria)
	{
		$pos = 0;

		foreach ($criteria as $field => $exprValue) {
			$this->applyExprFromString($qb, $alias, $field, $exprValue, ':'.$field.($pos++));
		}
	}

	/**
	 * @param array $mapConverter
	 * @param string $dbDriver
	 * @param string $exprValue
	 * @return boolean
	 */
	protected function supports(array $mapConverter, $dbDriver, $exprValue)
	{
		if (!in_array($dbDriver, $mapConverter['db_drivers'], SORT_REGULAR)) {
			return false;
		}

		return (boolean) preg_match($mapConverter['pattern'], $exprValue);
	}
}