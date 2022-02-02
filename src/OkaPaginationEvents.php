<?php

namespace Oka\PaginationBundle;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
final class OkaPaginationEvents
{
    /**
     * The PAGE will be emitted when a page is generated.
     *
     * @Event("Oka\PaginationBundle\Event\PageEvent")
     */
    public const PAGE = 'oka_pagination.page';
}
