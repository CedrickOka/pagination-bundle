<?php

declare(strict_types=1);

namespace Oka\PaginationBundle\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author Cedrick Oka Baidai <baidai.cedric@veone.net>
 *
 * @ORM\Entity
 *
 * @ORM\Table(name="page")
 */
class Page
{
    /**
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected int $number;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     *
     * @var \DateTime
     */
    protected $createdAt;
}
