<?php

namespace Oka\PaginationBundle\Tests\Pagination\FilterExpression\DBAL;

use Oka\PaginationBundle\Pagination\FilterExpression\DBAL\EqualFilterExpression;
use Oka\PaginationBundle\Tests\Document\Page;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class EqualFilterExpressionTest extends KernelTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    protected $documentManager;

    public function setUp(): void
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
        $filterExpression = new EqualFilterExpression();

        $this->assertEquals(true, $filterExpression->supports($this->entityManager->createQueryBuilder(), 'eq(text)'));
        $this->assertEquals(true, $filterExpression->supports($this->documentManager->createQueryBuilder(Page::class), 'eq(text)'));
    }

    /**
     * @covers
     */
    public function testThatFilterCanEvaluateOrmExpression()
    {
        $filterExpression = new EqualFilterExpression();
        $result = $filterExpression->evaluate($this->entityManager->createQueryBuilder(), 'p.field', 'eq(text)', 'string');
        /** @var \Doctrine\ORM\Query\Expr $expr */
        $expr = $result->getExpr();

        $this->assertEquals('p.field = ?1', $expr->__toString());
        $this->assertContains('text', $result->getParameters());
    }

    /**
     * @covers
     */
    public function testThatFilterCanEvaluateOdmExpression()
    {
        $filterExpression = new EqualFilterExpression();
        $result = $filterExpression->evaluate($this->documentManager->createQueryBuilder(Page::class), 'field', 'eq(text)', 'string');
        /** @var \Doctrine\ODM\MongoDB\Query\Expr $expr */
        $expr = $result->getExpr();

        $this->assertEquals(['field' => 'text'], $expr->getQuery());
    }

    /**
     * @covers
     */
    public function testThatFilterCastValue()
    {
        $filterExpression = new EqualFilterExpression();
        $result = $filterExpression->evaluate($this->entityManager->createQueryBuilder(), 'p.field', sprintf('eq(%s)', date('c')), 'datetime');
        /** @var \Doctrine\ORM\Query\Expr $expr */
        $expr = $result->getExpr();

        $this->assertEquals('p.field = ?1', $expr->__toString());
        $this->assertContainsOnlyInstancesOf(\DateTime::class, $result->getParameters());
    }
}
