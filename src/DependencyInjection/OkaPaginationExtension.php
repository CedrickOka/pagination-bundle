<?php

declare(strict_types=1);

namespace Oka\PaginationBundle\DependencyInjection;

use Oka\PaginationBundle\Pagination\FilterExpression\FilterExpressionInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class OkaPaginationExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $container
            ->registerForAutoconfiguration(FilterExpressionInterface::class)
            ->addTag('oka_pagination.filter_expression');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.yml');

        // Configure pagination manager
        $paginationManagers = [
            '_defaults' => [
                'db_driver' => $config['db_driver'],
                'item_per_page' => $config['item_per_page'],
                'max_page_number' => $config['max_page_number'],
                'sort' => $config['sort'],
                'query_mappings' => $config['query_mappings'],
                'filters' => $config['filters'],
                'object_manager_name' => $config['object_manager_name'],
                'twig' => $config['twig'],
            ],
        ];

        if (false === empty($config['pagination_managers'])) {
            $paginationManagers = array_merge($paginationManagers, $config['pagination_managers']);
        }

        $container->setParameter('oka_pagination.pagination_managers', $paginationManagers);

        // Twig Configuration
        $container->setParameter('oka_pagination.twig.enabled', $config['twig']['enabled']);
    }
}
