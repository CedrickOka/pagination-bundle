<?php

declare(strict_types=1);

namespace Oka\PaginationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class DoctrineRegistryServiceLocatorPass implements CompilerPassInterface
{
    public static array $doctrineDrivers = [
        'orm' => [
            'registry' => 'doctrine',
            'tag' => 'doctrine.event_subscriber',
        ],
        'mongodb' => [
            'registry' => 'doctrine_mongodb',
            'tag' => 'doctrine_mongodb.odm.event_subscriber',
        ],
    ];

    public function process(ContainerBuilder $container)
    {
        $locateableServices = [];

        foreach (static::$doctrineDrivers as $key => $dbDriver) {
            if (false === $container->hasDefinition($dbDriver['registry'])) {
                continue;
            }

            $locateableServices[$key] = new Reference($dbDriver['registry']);
        }

        $definition = $container->getDefinition('oka_pagination.pagination_manager');
        $definition->replaceArgument(0, ServiceLocatorTagPass::register($container, $locateableServices));
    }
}
