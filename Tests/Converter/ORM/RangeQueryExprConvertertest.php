<?php
namespace Oka\PaginationBundle\Tests\Converter\ORM;

use Oka\PaginationBundle\Converter\ORM\RangeQueryExprConverter;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class RangeQueryExprConvertertest extends KernelTestCase
{
	public function testApply()
	{
		$value = null;
		$converter = new RangeQueryExprConverter();
		/** @var \Doctrine\ORM\Query\Expr\Comparison $expr */
		$expr = $converter->apply('orm', 'c', 'field', 'range[v1,v2]', null, $value);
		
		$this->assertEquals([':field1' => 'v1', ':field2' => 'v2'], $value);
		$this->assertEquals('c.field >= :field1 AND c.field <= :field2', $expr->__toString());
		
		$value = null;
		/** @var \Doctrine\ORM\Query\Expr\Comparison $expr */
		$expr = $converter->apply('orm', 'c', 'field', 'range[v1,]', null, $value);
		
		$this->assertEquals([':field' => 'v1'], $value);
		$this->assertEquals('c.field >= :field', $expr->__toString());
		
		$value = null;
		/** @var \Doctrine\ORM\Query\Expr\Comparison $expr */
		$expr = $converter->apply('orm', 'c', 'field', 'range[,v2]', null, $value);
		
		$this->assertEquals([':field' => 'v2'], $value);
		$this->assertEquals('c.field <= :field', $expr->__toString());
		
		$value = null;
		/** @var \Doctrine\ORM\Query\Expr\Comparison $expr */
		$expr = $converter->apply('orm', 'c', 'field', 'range]v1,v2[', null, $value);
		
		$this->assertEquals([':field1' => 'v1', ':field2' => 'v2'], $value);
		$this->assertEquals('c.field > :field1 AND c.field < :field2', $expr->__toString());
		
		$value = null;
		/** @var \Doctrine\ORM\Query\Expr\Comparison $expr */
		$expr = $converter->apply('orm', 'c', 'field', 'range]v1,]', null, $value);
		
		$this->assertEquals([':field' => 'v1'], $value);
		$this->assertEquals('c.field > :field', $expr->__toString());
		
		$value = null;
		/** @var \Doctrine\ORM\Query\Expr\Comparison $expr */
		$expr = $converter->apply('orm', 'c', 'field', 'range],v2[', null, $value);
		
		$this->assertEquals([':field' => 'v2'], $value);
		$this->assertEquals('c.field < :field', $expr->__toString());
	}
}