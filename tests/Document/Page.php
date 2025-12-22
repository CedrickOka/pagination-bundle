<?php

namespace Oka\PaginationBundle\Tests\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @author Cedrick Oka Baidai <baidai.cedric@veone.net>
 */
#[MongoDB\Document(collection: 'page')]
class Page
{
    #[MongoDB\Id]
    protected string $id;

    #[MongoDB\Field(type: 'int')]
    protected int $number;

    #[MongoDB\Field(name: 'created_at', type: 'date')]
    protected \DateTime $createdAt;
}
