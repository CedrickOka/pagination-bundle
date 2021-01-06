<?php
namespace Oka\PaginationBundle\DependencyInjection\Compiler;

use Oka\PaginationBundle\OkaPaginationEvents;
use Oka\PaginationBundle\EventListener\PageListener;
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
		
		if (false === $container->hasDefinition('twig')) {
			return;
		}
		
		$extension = $container->register('oka_pagination.twig.extension', PaginationExtension::class);
		$extension->addArgument(new Reference('oka_pagination.pagination_manager'));
		$extension->addTag('twig.extension');
		
		$listener = $container->register('oka_pagination.page.event_listener', PageListener::class);
		$listener->replaceArgument(0, new Reference('twig'));
		$listener->addTag('kernel.event_listener', ['event' => OkaPaginationEvents::PAGE, 'method' => 'onPage']);
	}
}
