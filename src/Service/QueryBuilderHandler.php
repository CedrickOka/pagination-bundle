<?php
namespace Oka\PaginationBundle\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Utility\PersisterHelper;
use Oka\PaginationBundle\Converter\QueryExprConverterInterface;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class QueryBuilderHandler
{
	protected $mapConverters;

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
	public function applyExprFromString($qb, string $alias, string $field, string $exprValue, string $namedParameter = null) :void
	{
		if (!$qb instanceof QueryBuilder && !$qb instanceof Builder) {
			throw new \InvalidArgumentException(sprintf('Argument 1 passed to "%s" must be an instance of "%s" or "%s", "%s" given.', __METHOD__, QueryBuilder::class, Builder::class, gettype($qb)));
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
				
				$expr = $converter->apply($qb, $alias, $field, $exprValue, $namedParameter, $value);
				break;
			}
		}
		
		if ($qb instanceof QueryBuilder) {
		    $type = null;
		    
		    if ($entity = $qb->getRootEntities()[0] ?? null) {
		        $em = $qb->getEntityManager();
		        $types = $this->getTypes($field, $value, $em, $em->getClassMetadata($entity));
		        $type = $types[0] ?? null;
		    }
		    
			if (false === isset($expr)) {
			    $expr = $qb->expr()->eq($alias.'.'.$field, $namedParameter);
			    $qb->setParameter($namedParameter, $exprValue, $type);
			} else {
				switch (true) {
					case is_array($value):
						foreach ($value as $key => $val) {
							$qb->setParameter($key, $val);
						}
						break;
						
					case null !== $value:
					    $qb->setParameter($namedParameter, $value, $type);
						break;
				}
			}
			
			$qb->andWhere($expr);
		} else {
		    $qb->addAnd(isset($expr) ? $expr : $qb->expr()->field($field)->equals($value));
	   }
	}

	/**
	 * Apply Expr() object in query builder with array of criteria
	 *
	 * @param QueryBuilder|Builder $qb
	 * @param string $alias
	 * @param array $criteria
	 */
	public function applyExprFromArray($qb, string $alias, array $criteria)
	{
	    $pos = 0;
	    
		foreach ($criteria as $field => $exprValue) {
		    $this->applyExprFromString($qb, $alias, $field, $exprValue, ':'.$field.($pos++));
		}
	}

	protected function supports(array $mapConverter, $dbDriver, $exprValue) :bool
	{
		if (false === in_array($dbDriver, $mapConverter['db_drivers'], SORT_REGULAR)) {
			return false;
		}
		
		if (false === is_string($exprValue)) {
			return false;
		}
		
		return (bool) preg_match($mapConverter['pattern'], $exprValue);
	}
	
	/**
	 * Infers field types to be used by parameter type casting.
	 *
	 * @param string        $field
	 * @param mixed         $value
	 * @param ClassMetadata $class
	 *
	 * @return array
	 *
	 * @throws \Doctrine\ORM\Query\QueryException
	 */
	private function getTypes($field, $value, EntityManagerInterface $em, ClassMetadata $class)
	{
	    $types = [];
	    
	    switch (true) {
	        case (isset($class->fieldMappings[$field])):
	            $types = array_merge($types, [$class->fieldMappings[$field]['type']]);
	            break;
	            
	        case (isset($class->associationMappings[$field])):
	            $assoc = $class->associationMappings[$field];
	            $class = $em->getClassMetadata($assoc['targetEntity']);
	            
	            if (! $assoc['isOwningSide']) {
	                $assoc = $class->associationMappings[$assoc['mappedBy']];
	                $class = $em->getClassMetadata($assoc['targetEntity']);
	            }
	            
	            $columns = $assoc['type'] === ClassMetadata::MANY_TO_MANY
	            ? $assoc['relationToTargetKeyColumns']
	            : $assoc['sourceToTargetKeyColumns'];
	            
	            foreach ($columns as $column){
	                $types[] = PersisterHelper::getTypeOfColumn($column, $class, $em);
	            }
	            break;
	            
	        default:
	            $types[] = null;
	            break;
	    }
	    
	    if (is_array($value)) {
	        return array_map(function ($type) {
	            $type = Type::getType($type);
	            
	            return $type->getBindingType() + Connection::ARRAY_PARAM_OFFSET;
	        }, $types);
	    }
	    
	    return $types;
	}
}
