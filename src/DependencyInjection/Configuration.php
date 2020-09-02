<?php
namespace Oka\PaginationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\IntegerNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

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
	    $treeBuilder = new TreeBuilder('oka_pagination');
	    /** @var \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $rootNode */
		$rootNode = $treeBuilder->getRootNode();
		
		$rootNode
    		->beforeNormalization()
	    		->always(static function($v){
	    		    if (false === isset($v['pagination_managers'])) {
	    		        return $v;
	    		    }
	    		    
	    		    foreach ($v['pagination_managers'] as $key => $config) {
	    		    	if (false === isset($config['class'])) {
	    		    		$v['pagination_managers'][$key]['class'] = $key;
	    		    	}
	    		    	
    		            if (true === isset($v['filters'])) {
    		                $v['pagination_managers'][$key]['filters'] = array_merge($v['filters'], $config['filters'] ?? []);
    		                
    		            }
    		            
    		            if (true === isset($v['sort'])) {
    		                $v['pagination_managers'][$key]['sort'] = array_merge($v['sort'], $config['sort'] ??[]);
    		            }
    		            
    		            if (true === isset($v['query_mappings'])) {
    		            	$v['pagination_managers'][$key]['query_mappings'] = array_merge($v['query_mappings'], $config['query_mappings'] ?? []);
    		            }
	    		    }
	    		    
	    		    return $v;
	    		})
	    	->end()
    		->addDefaultsIfNotSet()
    		->children()
	    		->append($this->getDBDriverNodeDefinition())
	    		->append($this->getObjectManageNameNodeDefinition())
	    		->append($this->getMaxPageNumberNodeDefinition())
	    		->append($this->getItemPerPageNodeDefinition())
	    		->append($this->getFiltersNodeDefinition())
	    		->append($this->getSortNodeDefinition())
	    		->append($this->getQueryMappingsNodeDefinition())
	    		->append($this->getTwigNodeDefinition())
	    		
	    		->arrayNode('pagination_managers')
		    		->treatNullLike([])
		    		->requiresAtLeastOneElement()
		    		->useAttributeAsKey('name')
		    		->validate()
		    			->ifTrue(static function($v){
		    				true === array_key_exists('_defaults', $v);
		    			})
		    			->thenInvalid('The word "_defaults" is a reserved pagination name, it cannot be used.')
		    		->end()
		    		->arrayPrototype()
			    		->children()
			    			->append($this->getDBDriverNodeDefinition())
			    			->append($this->getObjectManageNameNodeDefinition())
				    		->scalarNode('class')->isRequired()->cannotBeEmpty()->end()
				    		->append($this->getMaxPageNumberNodeDefinition())
				    		->append($this->getItemPerPageNodeDefinition())
				    		->append($this->getFiltersNodeDefinition())
				    		->append($this->getRouteNodeDefinition())
				    		->append($this->getSortNodeDefinition())
				    		->append($this->getQueryMappingsNodeDefinition())
				    		->append($this->getTwigNodeDefinition())
			    		->end()
		    		->end()
	    		->end()
			->end();
		
		return $treeBuilder;
	}
	
	protected function getDBDriverNodeDefinition() :NodeDefinition
	{
		$node = new ScalarNodeDefinition('db_driver');
		$node
			->validate()
				->ifNotInArray(self::$supportedDrivers)
				->thenInvalid('The driver %s is not supported. Please choose one of '.json_encode(self::$supportedDrivers))
			->end()
			->isRequired()
			->cannotBeEmpty()
			->cannotBeOverwritten()
		->end();
		
		return $node;
	}
	
	protected function getObjectManageNameNodeDefinition() :NodeDefinition
	{
		$node = new ScalarNodeDefinition('object_manager_name');
		$node
		  ->defaultNull()
		->end();
		
		return $node;
	}
	
	protected function getItemPerPageNodeDefinition() :NodeDefinition
	{
		$node = new IntegerNodeDefinition('item_per_page');
		$node
		  ->min(1)
		  ->defaultValue(10)
	   ->end();
		
		return $node;
	}
	
	protected function getMaxPageNumberNodeDefinition() :NodeDefinition
	{
		$node = new IntegerNodeDefinition('max_page_number');
		$node
		  ->min(1)
		  ->defaultValue(400)
	   ->end();
		
		return $node;
	}
	
	protected function getFiltersNodeDefinition() :NodeDefinition
	{
		$supportedTypes = ['array', 'boolean', 'bool', 'double', 'float', 'real', 'integer', 'int', 'string', 'datetime', 'object'];
		
		$node = new ArrayNodeDefinition('filters');
		$node
			->beforeNormalization()
				->always(static function($v){
					if (true === empty($v)) {
						return $v;
					}
					
					foreach ($v as $key => $config) {
						if (false === isset($config['property_name'])) {
							$v[$key]['property_name'] = $key;
						}
					}
					
					return $v;
				})
			->end()
			->treatNullLike([])
			->useAttributeAsKey('name')
			->arrayPrototype()
				->children()
					->scalarNode('property_name')->isRequired()->cannotBeEmpty()->end()
					
					->scalarNode('cast_type')
						->defaultValue('string')
						->validate()
							->ifNotInArray($supportedTypes)
							->thenInvalid('The type %s is not supported. Please choose one of '.json_encode($supportedTypes))
						->end()
					->end()
					
					->booleanNode('searchable')->defaultTrue()->end()
					
					->arrayNode('ordering')
						->canBeDisabled()
						->addDefaultsIfNotSet()
						->children()
							->enumNode('direction')
								->values(['asc', 'desc'])
								->defaultValue('asc')
							->end()
						->end()
					->end()
				->end()
			->end();
		
		return $node;
	}
	
	protected function getSortNodeDefinition() :NodeDefinition
	{
		$node = new ArrayNodeDefinition('sort');
		$node
			->addDefaultsIfNotSet()
			->children()
				->scalarNode('delimiter')->defaultValue(',')->end()
			->end();
		
		return $node;
	}
	
	protected function getRouteNodeDefinition() :NodeDefinition
	{
		$node = new ArrayNodeDefinition('route');
		$node
			->addDefaultsIfNotSet()
			->canBeEnabled()
			->children()
				->scalarNode('path')->isRequired()->cannotBeEmpty()->end()
				
				->arrayNode('defaults')
					->treatNullLike([])
					->scalarPrototype()
						->cannotBeEmpty()
					->end()
				->end()
				
				->arrayNode('requirements')
					->treatNullLike([])
					->scalarPrototype()
						->cannotBeEmpty()
					->end()
				->end()
				
				->arrayNode('options')
					->treatNullLike([])
					->scalarPrototype()
						->cannotBeEmpty()
					->end()
				->end()
				
				->arrayNode('schemes')
					->treatNullLike([])
					->scalarPrototype()
						->cannotBeEmpty()
					->end()
				->end()
				
				->arrayNode('methods')
					->treatNullLike([])
					->scalarPrototype()
						->cannotBeEmpty()
					->end()
				->end()
				
				->scalarNode('condition')->defaultValue('')->end()
			->end();
		
		return $node;
	}
	
	protected function getQueryMappingsNodeDefinition() :NodeDefinition
	{
		$node = new ArrayNodeDefinition('query_mappings');
		$node
			->addDefaultsIfNotSet()
			->children()
				->scalarNode('page')->cannotBeEmpty()->defaultValue('page')->end()
				->scalarNode('item_per_page')->cannotBeEmpty()->defaultValue('item_per_page')->end()
				->scalarNode('sort')->cannotBeEmpty()->defaultValue('sort')->end()
				->scalarNode('desc')->cannotBeEmpty()->defaultValue('desc')->end()
				->scalarNode('fields')->cannotBeEmpty()->defaultValue('fields')->end()
				->scalarNode('distinct')->cannotBeEmpty()->defaultValue('distinct')->end()
			->end();
		
		return $node;
	}
	
	protected function getTwigNodeDefinition() :NodeDefinition
	{
		$node = new ArrayNodeDefinition('twig');
		$node
			->addDefaultsIfNotSet()
			->canBeEnabled()
			->children()
				->scalarNode('template')
					->cannotBeEmpty()
					->defaultValue('@OkaPagination/widget/pagination.html.twig')
				->end()
			->end();
		
		return $node;
	}
}
