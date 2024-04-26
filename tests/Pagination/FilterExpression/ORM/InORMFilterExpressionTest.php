<?php

namespace Oka\PaginationBundle\Tests\Pagination\FilterExpression\ORM;

use Oka\PaginationBundle\Pagination\FilterExpression\ORM\InORMFilterExpression;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class InORMFilterExpressionTest extends KernelTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    public function setUp(): void
    {
        static::bootKernel();

        $this->entityManager = static::$container->get('doctrine.orm.entity_manager');
    }

    /**
     * @covers
     */
    public function testThatFilterCanSupportEvaluation()
    {
        $filterExpression = new InORMFilterExpression();

        $this->assertEquals(true, $filterExpression->supports($this->entityManager->createQueryBuilder(), 'in(1,2)'));
    }

    /**
     * @covers
     */
    public function testThatFilterCanEvaluateExpression()
    {
        $filterExpression = new InORMFilterExpression();
        $queryBuilder = $this->entityManager->createQueryBuilder();

        $result = $filterExpression->evaluate($queryBuilder, 'p.field', 'in(1,2)', 'int');
        /** @var \Doctrine\ORM\Query\Expr $expr */
        $expr = $result->getExpr();

        $this->assertEquals('p.field IN(?1,?2)', $expr->__toString());
        $this->assertEquals([1 => 1, 2 => 2], $result->getParameters());
    }
}
