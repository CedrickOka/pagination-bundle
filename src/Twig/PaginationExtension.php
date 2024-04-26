<?php

namespace Oka\PaginationBundle\Twig;

use Oka\PaginationBundle\Pagination\PaginationManager;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFunction;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class PaginationExtension extends AbstractExtension implements GlobalsInterface
{
    private $paginationManager;

    public function __construct(PaginationManager $paginationManager)
    {
        $this->paginationManager = $paginationManager;
    }

    public function getName()
    {
        return 'oka_pagination.twig_extension';
    }

    public function getGlobals(): array
    {
        return [
            'oka_pagination' => [
                'pages' => [],
            ],
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('paginate', [$this, 'renderCurrentPaginationView'], ['needs_environment' => true, 'is_safe' => ['html']]),
            new TwigFunction('paginate_*', [$this, 'renderView'], ['needs_environment' => true, 'is_safe' => ['html']]),
        ];
    }

    /**
     * Render current pagination widget view.
     */
    public function renderCurrentPaginationView(Environment $env, string $route, array $params = []): string
    {
        return $this->renderView($env, $this->getLocalGlobals($env)['current_manager_name'] ?? '_defaults', $route, $params);
    }

    /**
     * Render pagination widget view.
     *
     * @throws \InvalidArgumentException
     */
    public function renderView(Environment $env, string $managerName, string $route, array $parameters = []): string
    {
        $globals = $this->getLocalGlobals($env);

        if (false === isset($globals['pages'][$managerName])) {
            throw new \InvalidArgumentException(sprintf('The configuration name "%s" is not found in twig global variables "oka_pagination.pages".', $managerName));
        }

        /** @var \Oka\PaginationBundle\Pagination\Configuration $configuration */
        $configuration = $this->paginationManager->getConfiguration($managerName);

        return $env->render($configuration->getTwig()['template'], [
            'route' => $route,
            'managerName' => $managerName,
            'parameters' => $parameters,
            'context' => $globals['pages'][$managerName],
        ]);
    }

    private function getLocalGlobals(Environment $env): array
    {
        return $env->getGlobals()['oka_pagination'];
    }
}
