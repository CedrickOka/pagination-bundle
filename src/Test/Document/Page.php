<?php

namespace Oka\PaginationBundle\Test\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @author Cedrick Oka Baidai <baidai.cedric@veone.net>
 */
#[MongoDB\Document(collection: 'page')]
class Page
{
    /**
     * @var string
     */
    #[MongoDB\Id()]
    protected $id;

    /**
     * @var int
     */
    #[MongoDB\Field(type: 'int')]
    protected $number;

    /**
     * @var \DateTime
     */
    #[MongoDB\Field(name: 'created_at', type: 'date')]
    protected $createdAt;
}
