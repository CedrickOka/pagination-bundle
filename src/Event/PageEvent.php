<?php

namespace Oka\PaginationBundle\Event;

use Oka\PaginationBundle\Pagination\Configuration;
use Oka\PaginationBundle\Pagination\Page;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class PageEvent extends Event
{
    public function __construct(private string $managerName, private Configuration $configuration, private Page $page)
    {
    }

    public function getManagerName(): string
    {
        return $this->managerName;
    }

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function getPage(): Page
    {
        return $this->page;
    }
}
