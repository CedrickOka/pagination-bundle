<?php

declare(strict_types=1);

namespace Oka\PaginationBundle\Test\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @author Cedrick Oka Baidai <baidai.cedric@veone.net>
 *
 * @MongoDB\Document(collection="page")
 */
class Page
{
    /**
     * @MongoDB\Id
     *
     * @var string
     */
    protected $id;

    /**
     * @MongoDB\Field(type="integer")
     *
     * @var int
     */
    protected $number;

    /**
     * @MongoDB\Field(type="date", name="created_at")
     *
     * @var \DateTime
     */
    protected $createdAt;
}
