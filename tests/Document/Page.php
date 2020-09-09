<?php
namespace Oka\PaginationBundle\Tests\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * 
 * @author Cedrick Oka Baidai <baidai.cedric@veone.net>
 * 
 * @MongoDB\Document(collection="page")
 */
class Page
{
	/**
	 * @MongoDB\Id()
	 * @var string
	 */
	protected $id;
	
	/**
	 * @MongoDB\Field(type="int")
	 * @var int
	 */
	protected $number;
	
	/**
	 * @MongoDB\Field(name="created_at", type="date")
	 * @var \DateTime
	 */
	protected $createdAt;
}
