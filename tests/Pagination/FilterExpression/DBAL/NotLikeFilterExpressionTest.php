<?php

namespace Oka\PaginationBundle\Tests\Pagination\FilterExpression\DBAL;

use Oka\PaginationBundle\Pagination\FilterExpression\DBAL\NotLikeFilterExpression;
use Oka\PaginationBundle\Tests\Document\Page;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class NotLikeFilterExpressionTest extends KernelTestCase
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
        $filterExpression = new NotLikeFilterExpression();

        $this->assertEquals(true, $filterExpression->supports($this->entityManager->createQueryBuilder(), 'notLike(text)'));
        $this->assertEquals(true, $filterExpression->supports($this->documentManager->createQueryBuilder(Page::class), 'notLike(text)'));
    }

    /**
     * @covers
     */
    public function testThatFilterCanEvaluateOrmExpression()
    {
        $filterExpression = new NotLikeFilterExpression();
        $result = $filterExpression->evaluate($this->entityManager->createQueryBuilder(), 'p.field', 'notLike(text)', 'string');
        /** @var \Doctrine\ORM\Query\Expr $expr */
        $expr = $result->getExpr();

        $this->assertEquals('p.field NOT LIKE ?1', $expr->__toString());
        $this->assertContains('text', $result->getParameters());
    }

    /**
     * @covers
     */
    public function testThatFilterCanEvaluateOdmExpression()
    {
        $filterExpression = new NotLikeFilterExpression();
        $result = $filterExpression->evaluate($this->documentManager->createQueryBuilder(Page::class), 'field', 'notLike(text)', 'string');
        /** @var \Doctrine\ODM\MongoDB\Query\Expr $expr */
        $expr = $result->getExpr();

        $this->assertEquals(['$not' => ['$text' => ['$search' => 'text']]], $expr->getQuery());
    }
}
