<?php

namespace Oka\PaginationBundle\Tests\Pagination;

use Doctrine\ORM\Tools\SchemaTool;
use Oka\PaginationBundle\Pagination\PaginationManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class PaginationManagerTest extends KernelTestCase
{
    public function setUp(): void
    {
        $kernel = self::bootKernel();

        if ('test' !== $kernel->getEnvironment()) {
            throw new \LogicException('Execution only in Test environment possible!');
        }

        $this->initDatabase($kernel);
    }

    /**
     * @covers
     */
    public function testThatPaginateEntityPage()
    {
        $filterValue = sprintf('neq(%s)', date('c'));

        /** @var PaginationManager $paginationManager */
        $paginationManager = static::$container->get(PaginationManager::class);
        $request = new Request(['createdAt' => $filterValue, 'sort' => 'createdAt', 'desc' => 'number']);

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

        /** @var PaginationManager $paginationManager */
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

    private function initDatabase(KernelInterface $kernel): void
    {
        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        $metaData = $em->getMetadataFactory()->getAllMetadata();

        $schemaTool = new SchemaTool($em);
        $schemaTool->updateSchema($metaData);
    }
}
