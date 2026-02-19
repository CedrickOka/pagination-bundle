<?php

namespace Oka\PaginationBundle\Routing;

use Oka\PaginationBundle\Pagination\ConfigurationBag;
use Symfony\Bundle\FrameworkBundle\Routing\RouteLoaderInterface;
use Symfony\Component\Routing\RouteCollection;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
readonly class PageRouteLoader implements RouteLoaderInterface
{
    public function __construct(
        private ConfigurationBag $configurations,
    ) {
    }

    public function __invoke(): RouteCollection
    {
        $routes = new RouteCollection();

        /** @var ConfigurationBag $configuration */
        foreach ($this->configurations->all() as $key => $configuration) {
            if (!$route = $configuration->getRoute()) {
                continue;
            }

            $routes->add(sprintf('oka_pagination_%s_list', $this->underscore($key)), $route);
        }

        return $routes;
    }

    private function underscore(string $string): string
    {
        $string = preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $string);

        return strtolower($string);
    }
}
