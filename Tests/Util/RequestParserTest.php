<?php
namespace Oka\PaginationBundle\Tests\Util;

use Oka\PaginationBundle\Util\RequestParser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * 
 * @author cedrick
 * 
 */
class RequestParserTest extends KernelTestCase
{
	public function testparseQueryToArray() {
		$request = new Request();
		$request->query->set('sort', 'name,createdAt,updatedAt');
		
		$this->assertEquals([], RequestParser::parseQueryToArray($request, 'empty', ','));
		$this->assertEquals(['name', 'createdAt', 'updatedAt'], RequestParser::parseQueryToArray($request, 'sort', ','));
	}
}