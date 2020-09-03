<?php
namespace Oka\PaginationBundle\Tests\Pagination\FilterExpression\DBAL;

use Oka\PaginationBundle\Pagination\FilterExpression\DBAL\IsNotNullFilterExpression;
use Oka\PaginationBundle\Tests\Document\Page;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class IsNotNullFilterExpressionTest extends KernelTestCase
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
		$filterExpression = new IsNotNullFilterExpression();
		
		$this->assertEquals(true, $filterExpression->supports($this->entityManager->createQueryBuilder(), 'isNotNull()'));
		$this->assertEquals(true, $filterExpression->supports($this->documentManager->createQueryBuilder(Page::class), 'isNotNull()'));
	}
	
	/**
	 * @covers
	 */
	public function testThatFilterCanEvaluateOrmExpression()
	{
		$filterExpression = new IsNotNullFilterExpression();
		$result = $filterExpression->evaluate($this->entityManager->createQueryBuilder(), 'p.field', 'isNotNull()', 'string');
		
		$this->assertEquals('p.field IS NOT NULL', $result->getExpr());
		$this->assertEmpty($result->getValues());
	}
	
	/**
	 * @covers
	 */
	public function testThatFilterCanEvaluateOdmExpression()
	{
		$filterExpression = new IsNotNullFilterExpression();
		$result = $filterExpression->evaluate($this->documentManager->createQueryBuilder(Page::class), 'field', 'isNotNull()', 'string');
		/** @var \Doctrine\ODM\MongoDB\Query\Expr $expr */
		$expr = $result->getExpr();
		
		$this->assertEquals(['field' => ['$ne' => null]], $expr->getQuery());
	}
}
