<?php
namespace Oka\PaginationBundle\Service;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class PaginationManagerBag extends ParameterBag
{
	public function __construct(array $parameters = [])
	{
		parent::__construct($parameters);
	}
}
