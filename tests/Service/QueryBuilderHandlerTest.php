<?php
namespace Oka\PaginationBundle\Tests\Service;

use Oka\PaginationBundle\Service\QueryBuilderHandler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class QueryBuilderHandlerTest extends KernelTestCase
{
	protected function setUp() :void
	{
	    parent::setUp();
	    
		static::bootKernel();
	}
	
	public function testApplyExprFromString()
	{
	    /** @var \Doctrine\ORM\EntityManagerInterface $em */
	    $em = static::$container->get('doctrine.orm.entity_manager');
	    $qb = $em->createQueryBuilder();
	    
	    /** @var QueryBuilderHandler $handler */
	    $handler = static::$container->get(QueryBuilderHandler::class);
	    $handler->applyExprFromArray($qb, 't', ['enabled' => true, 'name' => 'like(www%)']);
		
		var_dump($qb->getDQLPart('where'));
		$this->assertEquals(true, preg_match('#^not\((.+)\)$#i', 'not(apapapa)'));
	}
}
