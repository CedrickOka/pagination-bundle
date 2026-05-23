<?php

namespace Oka\PaginationBundle\Test\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author Cedrick Oka Baidai <baidai.cedric@veone.net>
 */
#[ORM\Entity()]
#[ORM\Table(name: 'page')]
class Page
{
    /**
     * @var string
     */
    #[ORM\Id()]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected $id;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    protected $number;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    protected $createdAt;
}
