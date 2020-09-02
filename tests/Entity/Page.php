<?php
namespace Oka\PaginationBundle\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * 
 * @author Cedrick Oka Baidai <baidai.cedric@veone.net>
 * 
 * @ORM\Entity(collection="page")
 * @ORM\Table(name="page")
 */
class Page
{
	/**
	 * @ORM\Id()
	 * @var string
	 */
	protected $id;
	
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	protected $field;
	
	/**
	 * @ORM\Column(name="created_at", type="datetime")
	 * @var \DateTime
	 */
	protected $createdAt;
}
