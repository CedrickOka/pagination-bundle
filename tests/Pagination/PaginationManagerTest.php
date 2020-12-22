<?php
namespace Oka\PaginationBundle\Tests\Pagination;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Oka\PaginationBundle\Tests\Document\Page;
use Oka\PaginationBundle\Pagination\PaginationManager;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class PaginationManagerTest extends KernelTestCase
{
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $entityManager;
	
	/**
	 * @var \Doctrine\ODM\MongoDB\DocumentManager
	 */
	protected $documentManager;
	
	public function setUp() :void
	{
		static::bootKernel();
		
		$this->entityManager = static::$container->get('doctrine.orm.entity_manager');
		$this->documentManager = static::$container->get('doctrine_mongodb.odm.document_manager');
	}
	
	public function tearDown() :void
	{
		$this->entityManager = null;
		$this->documentManager = null;
	}
	
	/**
	 * @covers
	 */
	public function testThatPaginateEntityPage()
	{
		$this->markTestIncomplete();
		$filterValue = sprintf('neq(%s)', date('c'));
		
		/** @var \Oka\PaginationBundle\Pagination\PaginationManager $paginationManager */
		$paginationManager = static::$container->get('oka_pagination.pagination_manager');
		$request = new Request(['createdAt' => $filterValue, 'sort' => 'createdAt', 'desc' => 'number']);
		$page = $paginationManager->paginate(\Oka\PaginationBundle\Tests\Entity\Page::class, $request);
		
		$this->assertEquals(1, $page->getPage());
		$this->assertEquals(1, $page->getPageNumber());
		$this->assertEquals(0, $page->getFullyItems());
		$this->assertEquals(['createdAt' => $filterValue], $page->getFilters());
		
		$page = $paginationManager->paginate('page_orm', $request);
		$this->assertEquals(1, $page->getPage());
		$this->assertEquals(1, $page->getPageNumber());
		$this->assertEquals(0, $page->getFullyItems());
		$this->assertEquals(['createdAt' => $filterValue], $page->getFilters());
	}
	
	/**
	 * @covers
	 */
	public function testThatPaginateDocumentPage()
	{
		$filterValue = sprintf('neq(%s)', date('c'));
		
		/** @var \Oka\PaginationBundle\Pagination\PaginationManager $paginationManager */
		$paginationManager = static::$container->get(PaginationManager::class);
		$request = new Request(['createdAt' => $filterValue, 'sort' => 'createdAt', 'desc' => 'number']);
		
		$page = $paginationManager->paginate(\Oka\PaginationBundle\Tests\Document\Page::class, $request);
		$this->assertEquals(1, $page->getPage());
		$this->assertEquals(1, $page->getPageNumber());
		$this->assertEquals(0, $page->getFullyItems());
		$this->assertEquals(['createdAt' => $filterValue], $page->getFilters());
		
		$page = $paginationManager->paginate('page_mongodb', $request);
		$this->assertEquals(1, $page->getPage());
		$this->assertEquals(1, $page->getPageNumber());
		$this->assertEquals(0, $page->getFullyItems());
		$this->assertEquals(['createdAt' => $filterValue], $page->getFilters());
	}
}