<?php

namespace Oka\PaginationBundle\Tests\Pagination\FilterExpression\ORM;

use Oka\PaginationBundle\Pagination\FilterExpression\ORM\RegexpLikeFilterExpression;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class RegexpLikeFilterExpressionTest extends KernelTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected \Doctrine\ORM\EntityManager $entityManager;

    /**
     * @var RegexpLikeFilterExpression
     */
    protected RegexpLikeFilterExpression $filterExpression;

    public function setUp(): void
    {
        static::bootKernel();

        $this->entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
        $this->filterExpression = new RegexpLikeFilterExpression();
    }

    /**
     * @covers
     */
    public function testThatFilterCanSupportEvaluation()
    {
        $this->assertTrue($this->filterExpression->supports($this->entityManager->createQueryBuilder(), 'rLike(^text,)'));
        $this->assertTrue($this->filterExpression->supports($this->entityManager->createQueryBuilder(), 'rLike(^text,i)'));
    }

    /**
     * @covers
     */
    public function testThatFilterCanEvaluateExpression()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();

        $result = $this->filterExpression->evaluate($queryBuilder, 'p.field', 'rLike(^text,)', 'string');
        /** @var \Doctrine\ORM\Query\Expr $expr */
        $expr = $result->getExpr();

        $this->assertEquals('REGEXP_LIKE(p.field, \'^text\') = 1', $expr->__toString());
        $this->assertEmpty($result->getParameters());

        $result = $this->filterExpression->evaluate($queryBuilder, 'p.field', 'rLike(^text,i)', 'string');
        /** @var \Doctrine\ORM\Query\Expr $expr */
        $expr = $result->getExpr();

        $this->assertEquals('REGEXP_LIKE(p.field, \'^text\', \'i\') = 1', $expr->__toString());
        $this->assertEmpty($result->getParameters());
    }
}
