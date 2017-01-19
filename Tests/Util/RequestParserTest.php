<?php
namespace Oka\PaginationBundle\Tests\Util;

use Oka\ApiBundle\Util\RequestParser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * 
 * @author cedrick
 * 
 */
class RequestParserTest extends KernelTestCase
{
	public function testParseQuerytoArray() {
		$request = new Request();
		$request->query->set('sort', 'name,createdAt,updatedAt');
		
		$this->assertEquals([], RequestParser::parseQuerytoArray($request, 'empty', ','));
		$this->assertEquals(['name', 'createdAt', 'updatedAt'], RequestParser::parseQuerytoArray($request, 'sort', ','));
	}
}