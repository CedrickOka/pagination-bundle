<?php
namespace Oka\PaginationBundle\DependencyInjection;

use Oka\PaginationBundle\Twig\OkaPaginationExtension as TwigExtension;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\IntegerNodeDefinition;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root('oka_pagination');
		
		$rootNode
			->addDefaultsIfNotSet()
			->children()
				->append($this->getDBDriverNodeDefinition())
				->append($this->getEntityManageNameNodeDefinition())
				->append($this->getItemPerPageNodeDefinition())
				->append($this->getMaxPageNumberNodeDefinition())
				->append($this->getTemplateNodeDefinition())
				->append($this->getRequestNodeDefinition())
				->append($this->getSortNodeDefinition())
				->arrayNode('twig')
					->addDefaultsIfNotSet()
					->children()
						->booleanNode('enable_extension')->defaultTrue()->end()
						->booleanNode('enable_global')->defaultTrue()->end()
					->end()
				->end()
				->arrayNode('pagination_managers')
				 	->isRequired()
				 	->requiresAtLeastOneElement()
					->useAttributeAsKey('name')
					->prototype('array')
						->children()
							->append($this->getDBDriverNodeDefinition())
							->scalarNode('class')->isRequired()->cannotBeEmpty()->end()
							->append($this->getEntityManageNameNodeDefinition())
							->append($this->getItemPerPageNodeDefinition())
							->append($this->getMaxPageNumberNodeDefinition())
							->append($this->getTemplateNodeDefinition())
							->append($this->getRequestNodeDefinition())
// 							->arrayNode('request')
// 								->addDefaultsIfNotSet()
// 								->children()
// 									->append($this->getRequestNodeDefinition())
// 								->end()
// 							->end()
							->append($this->getSortNodeDefinition())
						->end()
					->end()
				->end()
			->end();
		
		return $treeBuilder;
	}
	
	public function getDBDriverNodeDefinition()
	{
		$supportedDrivers = ['orm', 'mongodb'];
		
		$node = new ScalarNodeDefinition('db_driver');
		$node
			->validate()
				->ifNotInArray($supportedDrivers)
				->thenInvalid('The driver %s is not supported. Please choose one of '.json_encode($supportedDrivers))
			->end()
			->cannotBeOverwritten()
			->isRequired()
			->cannotBeEmpty()
		->end();
		
		return $node;
	}
	
	public function getEntityManageNameNodeDefinition()
	{
		$node = new ScalarNodeDefinition('model_manager_name');
		$node->defaultNull()->end();
		
		return $node;
	}
	
	public function getItemPerPageNodeDefinition()
	{
		$node = new IntegerNodeDefinition('item_per_page');
		$node->min(1)->defaultValue(10)->end();
		
		return $node;
	}
	
	public function getMaxPageNumberNodeDefinition()
	{
		$node = new IntegerNodeDefinition('max_page_number');
		$node->min(1)->defaultValue(400)->end();
		
		return $node;
	}
	
	public function getTemplateNodeDefinition()
	{
		$node = new ScalarNodeDefinition('template');
		$node->cannotBeEmpty()->cannotBeEmpty()->defaultValue(TwigExtension::DEFAULT_TEMPLATE)->end();
		
		return $node;
	}
	
	public function getRequestNodeDefinition()
	{
// 		$node = new ArrayNodeDefinition('query_map');
// 		$node
// 			->addDefaultsIfNotSet()
// 			->children()
// 				->scalarNode('page')->cannotBeEmpty()->defaultValue('page')->end()
// 				->scalarNode('item_per_page')->cannotBeEmpty()->defaultValue('item_per_page')->end()
// 				->scalarNode('sort')->cannotBeEmpty()->defaultValue('sort')->end()
// 				->scalarNode('desc')->cannotBeEmpty()->defaultValue('desc')->end()
// 			->end()
// 		->end();
		
// 		return $node;
		
		$node = new ArrayNodeDefinition('request');
		$node
			->addDefaultsIfNotSet()
			->children()
				->arrayNode('query_map')
					->addDefaultsIfNotSet()
					->children()
						->scalarNode('page')->cannotBeEmpty()->defaultValue('page')->end()
						->scalarNode('item_per_page')->cannotBeEmpty()->defaultValue('item_per_page')->end()
						->scalarNode('sort')->cannotBeEmpty()->defaultValue('sort')->end()
						->scalarNode('desc')->cannotBeEmpty()->defaultValue('desc')->end()
					->end()
				->end()
			->end()
		->end();
		
		return $node;
	}
	
	public function getSortNodeDefinition()
	{
		$node = new ArrayNodeDefinition('sort');
		$node
			->addDefaultsIfNotSet()
			->children()
				->scalarNode('delimiter')->cannotBeEmpty()->defaultValue(',')->end()
				->arrayNode('attributes_availables')
					->treatNullLike([])
					->prototype('scalar')->end()
				->end()
			->end()
		->end();
		
		return $node;
	}
}