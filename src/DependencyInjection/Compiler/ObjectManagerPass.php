<?php
namespace Oka\PaginationBundle\DependencyInjection\Compiler;

use Oka\PaginationBundle\DependencyInjection\OkaPaginationExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class ObjectManagerPass implements CompilerPassInterface
{
	public function process(ContainerBuilder $container)
	{
		$driver = $container->getParameter('oka_pagination.db_driver');
		
		if (true === $container->has(OkaPaginationExtension::$doctrineDrivers[$driver]['registry'])) {
			$definition = $container->getDefinition('oka_pagination.default.object_manager');
			$definition->addArgument(new Parameter('oka_pagination.model_manager_name'));
			$definition->setFactory([new Reference(OkaPaginationExtension::$doctrineDrivers[$driver]['registry']), 'getManager']);
			$definition->setPublic(true);
		}
	}
}
