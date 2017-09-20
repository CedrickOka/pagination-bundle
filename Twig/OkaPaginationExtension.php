<?php
namespace Oka\PaginationBundle\Twig;

use Oka\PaginationBundle\Service\PaginationManager;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class OkaPaginationExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
	const TWIG_GLOBAL_VAR_NAME = 'oka_pagination';
	const DEFAULT_TEMPLATE = 'OkaPaginationBundle:Pagination:paginate.html.twig';
	
	/**
	 * @var PaginationManager $paginationManager
	 */
	protected $paginationManager;
	
	/**
	 * @param PaginationManager $paginationManager
	 */
	public function __construct(PaginationManager $paginationManager) {
		$this->paginationManager = $paginationManager;
	}
	
	public function getName()
	{
		return 'oka_pagination.twig_extension';
	}
	
	public function getGlobals()
	{
		return [self::TWIG_GLOBAL_VAR_NAME => []];
	}
	
	public function getFunctions()
	{
		return [
				new \Twig_SimpleFunction('paginate', [$this, 'renderDefaultBlock'], ['needs_environment' => true, 'is_safe' => ['html']]),
				new \Twig_SimpleFunction('paginate_*', [$this, 'renderBlock'], ['needs_environment' => true, 'is_safe' => ['html']])
		];
	}
	
	public function renderDefaultBlock(\Twig_Environment $env, $route, array $params = [])
	{
		return $this->renderBlock($env, $this->paginationManager->getCurrentManagerName(), $route, $params);
	}
	
	public function renderBlock(\Twig_Environment $env, $name, $route, array $params = [])
	{
		$managerConfig = $this->paginationManager->getManagerConfig($name);
		$paginationStore = $this->paginationManager->getPaginationStore();
		
		if (!isset($paginationStore[$name])) {
			throw new \InvalidArgumentException(sprintf('The "%s" configuration key not found in pagination result set store.', $name));
		}
		
		return $env->render($managerConfig['template'] ?: self::DEFAULT_TEMPLATE, [
				'route' => $route, 
				'params' => $params,
				'managerName' => $name,
				'context' => $paginationStore[$name]
		]);
	}
}
