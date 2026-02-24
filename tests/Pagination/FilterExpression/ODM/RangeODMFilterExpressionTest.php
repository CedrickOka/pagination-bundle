<?php

declare(strict_types=1);

namespace Oka\PaginationBundle\Tests\Pagination\FilterExpression\ODM;

use Oka\PaginationBundle\Pagination\FilterExpression\ODM\RangeODMFilterExpression;
use Oka\PaginationBundle\Tests\Document\Page;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class RangeODMFilterExpressionTest extends KernelTestCase
{
    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    protected \Doctrine\ODM\MongoDB\DocumentManager $documentManager;

    public function setUp(): void
    {
        static::bootKernel();

        $this->documentManager = static::getContainer()->get('doctrine_mongodb.odm.document_manager');
    }

    /**
     * @covers
     */
    public function testThatFilterCanSupportEvaluation()
    {
        $filterExpression = new RangeODMFilterExpression();

        $this->assertTrue($filterExpression->supports($this->documentManager->createQueryBuilder(Page::class), 'range[1,2]'));
    }

    /**
     * @covers
     */
    public function testThatFilterCanEvaluateExpression()
    {
        $filterExpression = new RangeODMFilterExpression();
        $queryBuilder = $this->documentManager->createQueryBuilder(Page::class);

        $result = $filterExpression->evaluate($queryBuilder, 'field', 'range[1,2]', 'int');
        /** @var \Doctrine\ODM\MongoDB\Query\Expr $expr */
        $expr = $result->getExpr();

        $this->assertEquals(['$and' => [['field' => ['$gte' => 1]], ['field' => ['$lte' => 2]]]], $expr->getQuery());
        $this->assertEmpty($result->getParameters());

        $result = $filterExpression->evaluate($queryBuilder, 'field', 'range]1,2]', 'int');
        /** @var \Doctrine\ODM\MongoDB\Query\Expr $expr */
        $expr = $result->getExpr();

        $this->assertEquals(['$and' => [['field' => ['$gt' => 1]], ['field' => ['$lte' => 2]]]], $expr->getQuery());
        $this->assertEmpty($result->getParameters());

        $result = $filterExpression->evaluate($queryBuilder, 'field', 'range[1,2[', 'int');
        /** @var \Doctrine\ODM\MongoDB\Query\Expr $expr */
        $expr = $result->getExpr();

        $this->assertEquals(['$and' => [['field' => ['$gte' => 1]], ['field' => ['$lt' => 2]]]], $expr->getQuery());
        $this->assertEmpty($result->getParameters());

        $result = $filterExpression->evaluate($queryBuilder, 'field', 'range]1,2[', 'int');
        /** @var \Doctrine\ODM\MongoDB\Query\Expr $expr */
        $expr = $result->getExpr();

        $this->assertEquals(['$and' => [['field' => ['$gt' => 1]], ['field' => ['$lt' => 2]]]], $expr->getQuery());
        $this->assertEmpty($result->getParameters());

        $result = $filterExpression->evaluate($queryBuilder, 'field', 'range]1,[', 'int');
        /** @var \Doctrine\ODM\MongoDB\Query\Expr $expr */
        $expr = $result->getExpr();

        $this->assertEquals(['field' => ['$gt' => 1]], $expr->getQuery());
        $this->assertEmpty($result->getParameters());

        $result = $filterExpression->evaluate($queryBuilder, 'field', 'range],2[', 'int');
        /** @var \Doctrine\ODM\MongoDB\Query\Expr $expr */
        $expr = $result->getExpr();

        $this->assertEquals(['field' => ['$lt' => 2]], $expr->getQuery());
        $this->assertEmpty($result->getParameters());
    }
}
