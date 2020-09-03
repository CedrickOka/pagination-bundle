<?php
namespace Oka\PaginationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class FilterExpressionsPass implements CompilerPassInterface
{
	use PriorityTaggedServiceTrait;
	
	public function process(ContainerBuilder $container)
	{
		if (false === $container->hasDefinition('oka_pagination.filter_expression_handler')) {
			return;
		}
		
		$definition = $container->getDefinition('oka_pagination.filter_expression_handler');
		
		foreach ($container->findTaggedServiceIds('oka_pagination.filter_expression') as $id => $tags) {
			$definition->addMethodCall('addFilterExpression', [new Reference($id)]);
		}
	}
}
