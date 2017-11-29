<?php
namespace Oka\PaginationBundle\Tests\Converter;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Oka\PaginationBundle\Converter\LikeQueryExprConverter;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class LikeQueryExprConvertertest extends KernelTestCase
{
	public function testApply()
	{
		$value = null;
		$converter = new LikeQueryExprConverter();
		/** @var \Doctrine\ORM\Query\Expr\Comparison $expr */
		$expr = $converter->apply('orm', 'c', 'field', 'like(field)', null, $value);
		
		$this->assertEquals('field', $value);
		$this->assertEquals('c.field LIKE :field', $expr->__toString());
	}
}