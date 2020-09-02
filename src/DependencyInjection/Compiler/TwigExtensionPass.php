<?php
namespace Oka\PaginationBundle\DependencyInjection\Compiler;

use Oka\PaginationBundle\Twig\PaginationExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class TwigExtensionPass implements CompilerPassInterface
{
	public function process(ContainerBuilder $container)
	{
		if (false === $container->getParameter('oka_pagination.twig.enabled')) {
			return;
		}
		
		if (false === $container->hasDefinition('twig.runtime_loader')) {
			return;
		}
				
		$definition = $container->register('oka_pagination.twig.extension', PaginationExtension::class);
		$definition->addArgument(new Reference('oka_pagination.pagination_manager'));
		$definition->addTag('twig.extension');
	}
}
