<?php
namespace Oka\PaginationBundle\Tests\Pagination\FilterExpression\DBAL;

use Oka\PaginationBundle\Pagination\FilterExpression\DBAL\IsNullFilterExpression;
use Oka\PaginationBundle\Tests\Document\Page;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class IsNullFilterExpressionTest extends KernelTestCase
{
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $entityManager;
	
	/**
	 * @var \Doctrine\ODM\MongoDB\DocumentManager
	 */
	protected $documentManager;
	
	public function setUp() :void
	{
		static::bootKernel();
		
		$this->entityManager = static::$container->get('doctrine.orm.entity_manager');
		$this->documentManager = static::$container->get('doctrine_mongodb.odm.document_manager');
	}
	
	/**
	 * @covers
	 */
	public function testThatFilterCanSupportEvaluation()
	{
		$filterExpression = new IsNullFilterExpression();
		
		$this->assertEquals(true, $filterExpression->supports($this->entityManager->createQueryBuilder(), 'isNull()'));
		$this->assertEquals(true, $filterExpression->supports($this->documentManager->createQueryBuilder(Page::class), 'isNull()'));
	}
	
	/**
	 * @covers
	 */
	public function testThatFilterCanEvaluateOrmExpression()
	{
		$filterExpression = new IsNullFilterExpression();
		$result = $filterExpression->evaluate($this->entityManager->createQueryBuilder(), 'p.field', 'isNull()', 'string');
		
		$this->assertEquals('p.field IS NULL', $result->getExpr());
		$this->assertEmpty($result->getParameters());
	}
	
	/**
	 * @covers
	 */
	public function testThatFilterCanEvaluateOdmExpression()
	{
		$filterExpression = new IsNullFilterExpression();
		$result = $filterExpression->evaluate($this->documentManager->createQueryBuilder(Page::class), 'field', 'isNull()', 'string');
		/** @var \Doctrine\ODM\MongoDB\Query\Expr $expr */
		$expr = $result->getExpr();
		
		$this->assertEquals(['field' => null], $expr->getQuery());
	}
}
