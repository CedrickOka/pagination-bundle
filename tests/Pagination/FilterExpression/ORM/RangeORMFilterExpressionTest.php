<?php

namespace Oka\PaginationBundle\Tests\Pagination\FilterExpression\ORM;

use Oka\PaginationBundle\Pagination\FilterExpression\ORM\RangeORMFilterExpression;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class RangeORMFilterExpressionTest extends KernelTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    public function setUp(): void
    {
        static::bootKernel();

        $this->entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * @covers
     */
    public function testThatFilterCanSupportEvaluation()
    {
        $filterExpression = new RangeORMFilterExpression();

        $this->assertEquals(true, $filterExpression->supports($this->entityManager->createQueryBuilder(), 'range[1,2]'));
    }

    /**
     * @covers
     */
    public function testThatFilterCanEvaluateExpression()
    {
        $filterExpression = new RangeORMFilterExpression();
        $queryBuilder = $this->entityManager->createQueryBuilder();

        $result = $filterExpression->evaluate($queryBuilder, 'p.field', 'range[1,2]', 'int');
        /** @var \Doctrine\ORM\Query\Expr $expr */
        $expr = $result->getExpr();

        $this->assertEquals('p.field >= ?1 AND p.field <= ?2', $expr->__toString());
        $this->assertEquals([1 => 1, 2 => 2], $result->getParameters());

        $result = $filterExpression->evaluate($queryBuilder, 'p.field', 'range]1,2]', 'int');
        /** @var \Doctrine\ORM\Query\Expr $expr */
        $expr = $result->getExpr();

        $this->assertEquals('p.field > ?1 AND p.field <= ?2', $expr->__toString());
        $this->assertEquals([1 => 1, 2 => 2], $result->getParameters());

        $result = $filterExpression->evaluate($queryBuilder, 'p.field', 'range[1,3[', 'int');
        /** @var \Doctrine\ORM\Query\Expr $expr */
        $expr = $result->getExpr();

        $this->assertEquals('p.field >= ?1 AND p.field < ?2', $expr->__toString());
        $this->assertEquals([1 => 1, 2 => 3], $result->getParameters());

        $result = $filterExpression->evaluate($queryBuilder, 'p.field', 'range]1,2[', 'int');
        /** @var \Doctrine\ORM\Query\Expr $expr */
        $expr = $result->getExpr();

        $this->assertEquals('p.field > ?1 AND p.field < ?2', $expr->__toString());
        $this->assertEquals([1 => 1, 2 => 2], $result->getParameters());

        $result = $filterExpression->evaluate($queryBuilder, 'p.field', 'range]1,[', 'int');
        /** @var \Doctrine\ORM\Query\Expr $expr */
        $expr = $result->getExpr();

        $this->assertEquals('p.field > ?1', $expr->__toString());
        $this->assertEquals([1 => 1], $result->getParameters());

        $result = $filterExpression->evaluate($queryBuilder, 'p.field', 'range],2[', 'int');
        /** @var \Doctrine\ORM\Query\Expr $expr */
        $expr = $result->getExpr();

        $this->assertEquals('p.field < ?1', $expr->__toString());
        $this->assertEquals([1 => 2], $result->getParameters());
    }
}
