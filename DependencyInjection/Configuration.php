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
	protected static $supportedDrivers = ['orm', 'mongodb'];
	
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
				->arrayNode('twig')
					->addDefaultsIfNotSet()
					->children()
						->booleanNode('enable_extension')->defaultTrue()->end()
					->end()
				->end()
				->arrayNode('query_expr_converters')
					->useAttributeAsKey('name')
					->treatNullLike([])
					->prototype('array')
						->children()
							->arrayNode('db_drivers')
								->treatNullLike(self::$supportedDrivers)
								->validate()
									->ifTrue(function($value){
										foreach ($value as $v) {
											if (false === in_array($v, self::$supportedDrivers, SORT_REGULAR)) {
												return true;
											}
										}
										return false;
									})
									->thenInvalid('Only following options are supported "%s".')
								->end()
								->prototype('scalar')->end()
							->end()
							->scalarNode('class')->isRequired()->end()
							->scalarNode('pattern')->isRequired()->end()
						->end()
					->end()
				->end()
				->arrayNode('pagination_managers')
					->treatNullLike([])
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
						->end()
					->end()
				->end()
			->end();
		
		return $treeBuilder;
	}
	
	protected function getDBDriverNodeDefinition()
	{
		$node = new ScalarNodeDefinition('db_driver');
		$node
			->validate()
				->ifNotInArray(self::$supportedDrivers)
				->thenInvalid('The driver %s is not supported. Please choose one of '.json_encode(self::$supportedDrivers))
			->end()
			->cannotBeOverwritten()
			->isRequired()
			->cannotBeEmpty()
		->end();
		
		return $node;
	}
	
	protected function getEntityManageNameNodeDefinition()
	{
		$node = new ScalarNodeDefinition('model_manager_name');
		$node->defaultNull()->end();
		
		return $node;
	}
	
	protected function getItemPerPageNodeDefinition()
	{
		$node = new IntegerNodeDefinition('item_per_page');
		$node->min(1)->defaultValue(10)->end();
		
		return $node;
	}
	
	protected function getMaxPageNumberNodeDefinition()
	{
		$node = new IntegerNodeDefinition('max_page_number');
		$node->min(1)->defaultValue(400)->end();
		
		return $node;
	}
	
	protected function getTemplateNodeDefinition()
	{
		$node = new ScalarNodeDefinition('template');
		$node
			->cannotBeEmpty()
			->defaultValue(TwigExtension::DEFAULT_TEMPLATE)
			->treatNullLike(TwigExtension::DEFAULT_TEMPLATE)
			->treatTrueLike(TwigExtension::DEFAULT_TEMPLATE)
			->treatFalseLike(TwigExtension::DEFAULT_TEMPLATE)
		->end();
		
		return $node;
	}
	
	protected function getRequestNodeDefinition()
	{
		$supportedTypes = ['boolean', 'bool', 'integer', 'int', 'float', 'double', 'string', 'datetime'];
		
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
						->arrayNode('filters')
							->treatNullLike([])
						 	->requiresAtLeastOneElement()
							->useAttributeAsKey('name')
							->prototype('array')
								->children()
									->scalarNode('type')
										->defaultValue('string')
										->validate()
											->ifNotInArray($supportedTypes)
											->thenInvalid('The type %s is not supported. Please choose one of '.json_encode($supportedTypes))
										->end()
									->end()
									->scalarNode('field')->defaultNull()->end()
								->end()
							->end()
						->end()
					->end()
				->end()
				->append($this->getSortNodeDefinition(false))
			->end()
		->end();
		
		return $node;
	}
	
	protected function getSortNodeDefinition()
	{
		$node = new ArrayNodeDefinition('sort');
		$node
			->info('Request sort configuration')
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
