<?php
namespace Oka\PaginationBundle\Tests\Pagination\FilterExpression\ORM;

use Oka\PaginationBundle\Pagination\FilterExpression\ORM\RangeFilterExpression;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class RangeFilterExpressionTest extends KernelTestCase
{
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $entityManager;
	
	public function setUp() :void
	{
		static::bootKernel();
		
		$this->entityManager = static::$container->get('doctrine.orm.entity_manager');
	}
	
	/**
	 * @covers
	 */
	public function testThatFilterCanEvaluateExpression()
	{
		$filterExpression = new RangeFilterExpression();
		$queryBuilder = $this->entityManager->createQueryBuilder();
		
		$result = $filterExpression->evaluate($queryBuilder, 'p.field', 'range[1,2]', 'int');
		/** @var \Doctrine\ORM\Query\Expr $expr */
		$expr = $result->getExpr();
		
		$this->assertEquals('p.field >= ? AND p.field <= ?', $expr->__toString());
		$this->assertEquals([1, 2], $result->getValues());
		
		$result = $filterExpression->evaluate($queryBuilder, 'p.field', 'range]1,2]', 'int');
		/** @var \Doctrine\ORM\Query\Expr $expr */
		$expr = $result->getExpr();
		
		$this->assertEquals('p.field > ? AND p.field <= ?', $expr->__toString());
		$this->assertEquals([1, 2], $result->getValues());
		
		$result = $filterExpression->evaluate($queryBuilder, 'p.field', 'range[1,2[', 'int');
		/** @var \Doctrine\ORM\Query\Expr $expr */
		$expr = $result->getExpr();
		
		$this->assertEquals('p.field >= ? AND p.field < ?', $expr->__toString());
		$this->assertEquals([1, 2], $result->getValues());
		
		$result = $filterExpression->evaluate($queryBuilder, 'p.field', 'range]1,2[', 'int');
		/** @var \Doctrine\ORM\Query\Expr $expr */
		$expr = $result->getExpr();
		
		$this->assertEquals('p.field > ? AND p.field < ?', $expr->__toString());
		$this->assertEquals([1, 2], $result->getValues());
		
		$result = $filterExpression->evaluate($queryBuilder, 'p.field', 'range]1,[', 'int');
		/** @var \Doctrine\ORM\Query\Expr $expr */
		$expr = $result->getExpr();
		
		$this->assertEquals('p.field > ?', $expr->__toString());
		$this->assertEquals([1], $result->getValues());
		
		$result = $filterExpression->evaluate($queryBuilder, 'p.field', 'range],2[', 'int');
		/** @var \Doctrine\ORM\Query\Expr $expr */
		$expr = $result->getExpr();
		
		$this->assertEquals('p.field < ?', $expr->__toString());
		$this->assertEquals([2], $result->getValues());
	}
}