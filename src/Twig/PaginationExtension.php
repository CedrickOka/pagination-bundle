<?php
namespace Oka\PaginationBundle\Twig;

use Oka\PaginationBundle\Pagination\PaginationManager;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class PaginationExtension extends AbstractExtension implements GlobalsInterface
{
	const DEFAULT_TEMPLATE = '@OkaPagination:widget:pagination.html.twig';
	
	private $paginationManager;
	
	public function __construct(PaginationManager $paginationManager)
	{
		$this->paginationManager = $paginationManager;
	}
	
	public function getName()
	{
		return 'oka_pagination.twig_extension';
	}
	
	public function getGlobals()
	{
		return ['oka_pagination' => []];
	}
	
	public function getFunctions()
	{
		return [
		    new TwigFunction('paginate', [$this, 'renderDefaultView'], ['needs_environment' => true, 'is_safe' => ['html']]),
		    new TwigFunction('paginate_*', [$this, 'renderView'], ['needs_environment' => true, 'is_safe' => ['html']])
		];
	}
	
	/**
	 * Render pagination widget view
	 * 
	 * @param \Twig_Environment $env
	 * @param string $route
	 * @param array $params
	 * @return string
	 */
	public function renderDefaultView(\Twig_Environment $env, $route, array $params = [])
	{
		return $this->renderView($env, $this->paginationManager->getLastManagerName(), $route, $params);
	}
	
	/**
	 * Render pagination widget view
	 * 
	 * @param \Twig_Environment $env
	 * @param string $name
	 * @param string $route
	 * @param array $params
	 * @throws \InvalidArgumentException
	 * @return string
	 */
	public function renderView(\Twig_Environment $env, $name, $route, array $params = [])
	{
		$config = $this->paginationManager->getConfiguration($name);
		$globals = $env->getGlobals();
		
		if (false === isset($globals['oka_pagination'][$name])) {
			throw new \InvalidArgumentException(sprintf('The "%s" configuration key not found in twig global variables "oka_pagination".', $name));
		}
		
		return $env->render($config->getTemplate(), [
			'route' => $route, 
			'params' => $params,
			'managerName' => $name,
			'context' => $globals['oka_pagination'][$name]
		]);
	}
}
