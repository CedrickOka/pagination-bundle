<?php
declare(strict_types=1);

namespace Oka\PaginationBundle\EventListener;

use Oka\PaginationBundle\Event\PageEvent;
use Twig\Environment;

class PageListener
{
    public function __construct(
        private readonly Environment $twig,
    ) {
    }

    public function onPage(PageEvent $event): void
    {
        $configuration = $event->getConfiguration();

        if (false === $configuration->getTwig()['enabled']) {
            return;
        }

        $globals = $this->twig->getGlobals()['oka_pagination'] ?? ['pages' => []];
        $globals['current_manager_name'] = $event->getManagerName();
        $globals['pages'][] = $event->getPage();

        $this->twig->addGlobal('oka_pagination', $globals);
    }
}
