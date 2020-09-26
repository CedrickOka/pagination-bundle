<?php
namespace Oka\PaginationBundle\Tests\Pagination\FilterExpression\DBAL;

use Oka\PaginationBundle\Pagination\FilterExpression\DBAL\NotEqualFilterExpression;
use Oka\PaginationBundle\Tests\Document\Page;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class NotEqualFilterExpressionTest extends KernelTestCase
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
		$filterExpression = new NotEqualFilterExpression();
		
		$this->assertEquals(true, $filterExpression->supports($this->entityManager->createQueryBuilder(), 'neq(text)'));
		$this->assertEquals(true, $filterExpression->supports($this->documentManager->createQueryBuilder(Page::class), 'neq(text)'));
	}
	
	/**
	 * @covers
	 */
	public function testThatFilterCanEvaluateOrmExpression()
	{
		$filterExpression = new NotEqualFilterExpression();
		$result = $filterExpression->evaluate($this->entityManager->createQueryBuilder(), 'p.field', 'neq(text)', 'string');
		/** @var \Doctrine\ORM\Query\Expr $expr */
		$expr = $result->getExpr();
		
		$this->assertEquals('p.field <> ?1', $expr->__toString());
		$this->assertContains('text', $result->getParameters());
	}
	
	/**
	 * @covers
	 */
	public function testThatFilterCanEvaluateOdmExpression()
	{
		$filterExpression = new NotEqualFilterExpression();
		$result = $filterExpression->evaluate($this->documentManager->createQueryBuilder(Page::class), 'field', 'neq(text)', 'string');
		/** @var \Doctrine\ODM\MongoDB\Query\Expr $expr */
		$expr = $result->getExpr();
		
		$this->assertEquals(['field' => ['$ne' => 'text']], $expr->getQuery());
	}
}
