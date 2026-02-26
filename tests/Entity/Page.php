<?php

declare(strict_types=1);

namespace Oka\PaginationBundle\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author Cedrick Oka Baidai <baidai.cedric@veone.net>
 */
#[ORM\Entity]
#[ORM\Table(name: 'page')]
class Page
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected int|string|null $id = null;

    #[ORM\Column(type: 'integer')]
    protected int $number;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    protected \DateTime $createdAt;
}
