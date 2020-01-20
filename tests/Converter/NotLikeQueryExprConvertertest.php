<?php
namespace Oka\PaginationBundle\Tests\Converter;

use Oka\PaginationBundle\Converter\DBAL\NotLikeQueryExprConverter;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class NotLikeQueryExprConvertertest extends KernelTestCase
{
	public function testApply()
	{
		$value = null;
		$converter = new NotLikeQueryExprConverter();
		/** @var \Doctrine\ORM\Query\Expr\Comparison $expr */
		$expr = $converter->apply('orm', 'c', 'field', 'notLike(field)', null, $value);
		
		$this->assertEquals('field', $value);
		$this->assertEquals('c.field NOT LIKE :field', $expr->__toString());
	}
}