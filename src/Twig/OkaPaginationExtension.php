<?php
namespace Oka\PaginationBundle\Twig;

if (false === class_exists('Twig_Extension')) {
    class OkaPaginationExtension {
        const TWIG_GLOBAL_VAR_NAME = 'oka_pagination';
        const DEFAULT_TEMPLATE = 'OkaPaginationBundle:Pagination:paginate.html.twig';
    }
    
    return;
}

use Oka\PaginationBundle\Service\PaginationManager;
use Twig\TwigFunction;
use Twig\Extension\GlobalsInterface;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class OkaPaginationExtension extends \Twig_Extension implements GlobalsInterface
{
	const TWIG_GLOBAL_VAR_NAME = 'oka_pagination';
	const DEFAULT_TEMPLATE = 'OkaPaginationBundle:Pagination:paginate.html.twig';
	
	protected $pm;
	
	public function __construct(PaginationManager $pm)
	{
		$this->pm = $pm;
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
		return $this->renderView($env, $this->pm->getLastManagerName(), $route, $params);
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
		$config = $this->pm->getManagerConfig($name);
		$globals = $env->getGlobals();
		
		if (!isset($globals[self::TWIG_GLOBAL_VAR_NAME][$name])) {
			throw new \InvalidArgumentException(sprintf('The "%s" configuration key not found in twig global variables "%s".', $name, self::TWIG_GLOBAL_VAR_NAME));
		}
		
		return $env->render($config['template'], [
			'route'       => $route, 
			'params'      => $params,
			'managerName' => $name,
			'context'     => $globals[self::TWIG_GLOBAL_VAR_NAME][$name]
		]);
	}
}
