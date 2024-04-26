<?php

namespace Oka\PaginationBundle\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author Cedrick Oka Baidai <baidai.cedric@veone.net>
 *
 * @ORM\Entity()
 *
 * @ORM\Table(name="page")
 */
class Page
{
    /**
     * @ORM\Id()
     *
     * @ORM\Column(type="integer")
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var string
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    protected $number;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     *
     * @var \DateTime
     */
    protected $createdAt;
}
