<?php
namespace Oka\PaginationBundle\Tests\Service;

use Oka\PaginationBundle\Service\QueryBuilderManipulator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class QueryBuilderManipulatorTest extends KernelTestCase
{
	/**
	 * @var EntityManager $em
	 */
	private $em;
	
	/**
	 * @var QueryBuilderManipulator $manipualtor
	 */
	private $manipualtor;
	
	protected function setUp()
	{
		static::bootKernel();
		
		$container = static::$kernel->getContainer();
		$this->em = $container->get('doctrine')->getManager();
		$this->manipualtor = $container->get('oka_pagination.query_builder_manipulator');
	}
	
	public function testApplyExprFromString()
	{
		$qb = $this->em->createQueryBuilder();
		$this->manipualtor->applyExprFromArray($qb, 't', ['enabled' => true, 'name' => 'like(www%)']);
		
		var_dump($qb->getDQLPart('where'));
		$this->assertEquals(true, preg_match('#^not\((.+)\)$#i', 'not(apapapa)'));
	}
}
