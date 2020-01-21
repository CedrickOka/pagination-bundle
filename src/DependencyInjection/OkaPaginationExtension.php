<?php
namespace Oka\PaginationBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class OkaPaginationExtension extends Extension
{
	/**
	 * @var array $doctrineDrivers
	 */
	public static $doctrineDrivers = [
		'orm' => [
			'registry' => 'doctrine',
			'tag' => 'doctrine.event_subscriber',
		],
		'mongodb' => [
			'registry' => 'doctrine_mongodb',
			'tag' => 'doctrine_mongodb.odm.event_subscriber',
		]
	];
	
	/**
	 * {@inheritDoc}
	 */
	public function load(array $configs, ContainerBuilder $container)
	{
		$configuration = new Configuration();
		$config = $this->processConfiguration($configuration, $configs);
		
		$loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
		$loader->load('services.yml');
		
		// Entity manager default name
		$container->setParameter('oka_pagination.db_driver', $config['db_driver']);
		$container->setParameter('oka_pagination.model_manager_name', $config['model_manager_name']);
		
		// Pagination default parameters
		$container->setParameter('oka_pagination.item_per_page', $config['item_per_page']);
		$container->setParameter('oka_pagination.max_page_number', $config['max_page_number']);
		$container->setParameter('oka_pagination.template', $config['template']);
		$container->setParameter('oka_pagination.request', $config['request']);
		
		// Twig Configuration
		$this->loadTwigConfiguration($config, $container);
		
		// Pagination bag
		$definition = $container->getDefinition('oka_pagination.manager_bag');
		$definition->replaceArgument(0, $config['pagination_managers']);
		
		// Query Expression Converters Configuration
		$this->loadQueryExprConverter($config, $container);
	}
	
	protected function loadTwigConfiguration(array $config, ContainerBuilder $container)
	{
		$container->setParameter('oka_pagination.twig.enable_extension', $config['twig']['enable_extension']);
		
		if (true === $config['twig']['enable_extension']) {
			$definition = new Definition('Oka\PaginationBundle\Twig\OkaPaginationExtension', [new Reference('oka_pagination.manager')]);
			$definition->addTag('twig.extension');
			$container->setDefinition('oka_pagination.twig.extension', $definition);
		}
	}
	
	protected function loadQueryExprConverter(array $config, ContainerBuilder $container)
	{
	    // TODO: Use class loader instead here
		$mapConverters = [
			[
				'db_drivers' => ['orm', 'mongodb'],
				'pattern' => \Oka\PaginationBundle\Converter\DBAL\EqualQueryExprConverter::PATTERN,
				'class' => \Oka\PaginationBundle\Converter\DBAL\EqualQueryExprConverter::class
			],
			[
				'db_drivers' => ['orm', 'mongodb'],
				'pattern' => \Oka\PaginationBundle\Converter\DBAL\NotEqualQueryExprConverter::PATTERN,
				'class' => \Oka\PaginationBundle\Converter\DBAL\NotEqualQueryExprConverter::class
			],
			[
				'db_drivers' => ['orm', 'mongodb'],
				'pattern' => \Oka\PaginationBundle\Converter\DBAL\LikeQueryExprConverter::PATTERN,
				'class' => \Oka\PaginationBundle\Converter\DBAL\LikeQueryExprConverter::class
			],
			[
				'db_drivers' => ['orm', 'mongodb'],
				'pattern' => \Oka\PaginationBundle\Converter\DBAL\NotLikeQueryExprConverter::PATTERN,
				'class' => \Oka\PaginationBundle\Converter\DBAL\NotLikeQueryExprConverter::class
			],
			[
			    'db_drivers' => ['orm', 'mongodb'],
			    'pattern' => \Oka\PaginationBundle\Converter\DBAL\IsNullQueryExprConverter::PATTERN,
			    'class' => \Oka\PaginationBundle\Converter\DBAL\IsNullQueryExprConverter::class
			],
			[
			    'db_drivers' => ['orm', 'mongodb'],
			    'pattern' => \Oka\PaginationBundle\Converter\DBAL\IsNotNullQueryExprConverter::PATTERN,
			    'class' => \Oka\PaginationBundle\Converter\DBAL\IsNotNullQueryExprConverter::class
			],
			[
				'db_drivers' => ['orm'],
				'pattern' => \Oka\PaginationBundle\Converter\ORM\RangeQueryExprConverter::PATTERN,
				'class' => \Oka\PaginationBundle\Converter\ORM\RangeQueryExprConverter::class
			],
			[
				'db_drivers' => ['mongodb'],
				'pattern' => \Oka\PaginationBundle\Converter\Mongodb\RangeQueryExprConverter::PATTERN,
				'class' => \Oka\PaginationBundle\Converter\Mongodb\RangeQueryExprConverter::class
			]
		];
		
		if (false === empty($config['query_expr_converters'])) {
			foreach ($config['query_expr_converters'] as $converter) {
				$mapConverters[] = $converter;
			}
		}
		
		$definition = $container->getDefinition('oka_pagination.query_builder_handler');
		$definition->replaceArgument(0, $mapConverters);
	}
}
