<?php
namespace Oka\PaginationBundle\Tests\Pagination\FilterExpression\ODM;

use Oka\PaginationBundle\Pagination\FilterExpression\ODM\InODMFilterExpression;
use Oka\PaginationBundle\Tests\Document\Page;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class InODMFilterExpressionTest extends KernelTestCase
{
	/**
	 * @var \Doctrine\ODM\MongoDB\DocumentManager
	 */
	protected $documentManager;
	
	public function setUp() :void
	{
		static::bootKernel();
		
		$this->documentManager = static::$container->get('doctrine_mongodb.odm.document_manager');
	}
	
	/**
	 * @covers
	 */
	public function testThatFilterCanSupportEvaluation()
	{
		$filterExpression = new InODMFilterExpression();
		
		$this->assertEquals(true, $filterExpression->supports($this->documentManager->createQueryBuilder(Page::class), 'in(1,2)'));
	}
	
	/**
	 * @covers
	 */
	public function testThatFilterCanEvaluateExpression()
	{
		$filterExpression = new InODMFilterExpression();
		$queryBuilder = $this->documentManager->createQueryBuilder(Page::class);
		
		$result = $filterExpression->evaluate($queryBuilder, 'field', 'in(1,2)', 'int');
		/** @var \Doctrine\ODM\MongoDB\Query\Expr $expr */
		$expr = $result->getExpr();
		
		$this->assertEquals(['field' => ['$in' => [1, 2]]], $expr->getQuery());
		$this->assertEmpty($result->getParameters());
	}
}