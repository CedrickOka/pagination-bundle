<?php

namespace Oka\PaginationBundle\Tests\Pagination\FilterExpression\DBAL;

use Oka\PaginationBundle\Pagination\FilterExpression\DBAL\LikeFilterExpression;
use Oka\PaginationBundle\Tests\Document\Page;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class LikeFilterExpressionTest extends KernelTestCase
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
        $filterExpression = new LikeFilterExpression();

        $this->assertEquals(true, $filterExpression->supports($this->entityManager->createQueryBuilder(), 'like(text)'));
        $this->assertEquals(true, $filterExpression->supports($this->documentManager->createQueryBuilder(Page::class), 'like(text)'));
    }

    /**
     * @covers
     */
    public function testThatFilterCanEvaluateOrmExpression()
    {
        $filterExpression = new LikeFilterExpression();
        $result = $filterExpression->evaluate($this->entityManager->createQueryBuilder(), 'p.field', 'like(text)', 'string');
        /** @var \Doctrine\ORM\Query\Expr $expr */
        $expr = $result->getExpr();

        $this->assertEquals('p.field LIKE ?1', $expr->__toString());
        $this->assertContains('text', $result->getParameters());
    }

    /**
     * @covers
     */
    public function testThatFilterCanEvaluateOdmExpression()
    {
        $filterExpression = new LikeFilterExpression();
        $result = $filterExpression->evaluate($this->documentManager->createQueryBuilder(Page::class), 'field', 'like(text)', 'string');
        /** @var \Doctrine\ODM\MongoDB\Query\Expr $expr */
        $expr = $result->getExpr();

        $this->assertEquals(['$text' => ['$search' => 'text']], $expr->getQuery());
    }
}
