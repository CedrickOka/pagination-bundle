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
// 			foreach ($tags as $tag) {
// 				if (false === isset($tag['db_drivers'])) {
// 					throw new \RuntimeException(sprintf('Each tag named "oka_pagination.filter_expression" of service "%s" must have at the attribute: "db_drivers".', $id));
// 				}
				
// 				$diff = array_diff($tag['db_drivers'], $dbDrivers);
				
// 				if (false === empty($diff)) {
// 					throw new \RuntimeException(sprintf('Tag named "oka_pagination.filter_expression" of service "%s" has defined the following values, "%s" which are not supported by the "db_drivers" attribute.', $id, implode(', ', $diff)));
// 				}
				
				
// 				$definition->addMethodCall('addFilterExpression', [new Reference($id)]);
// 			}
			
// 			$filterExpressions[] = [new Reference($id), $tag['db_drivers']];
			$definition->addMethodCall('addFilterExpression', [new Reference($id)]);
		}
	}
}
