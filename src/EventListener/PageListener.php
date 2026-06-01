<?php

declare(strict_types=1);

namespace Oka\PaginationBundle\EventListener;

use Oka\PaginationBundle\Event\PageEvent;

class PageListener
{
    /**
     * @var \Twig_Environment
     */
    private $twig;

    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
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
