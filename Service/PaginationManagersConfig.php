<?php
namespace Oka\PaginationBundle\Service;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * 
 * @author cedrick
 * 
 */
class PaginationManagersConfig extends ParameterBag
{
	public function __construct(array $parameters = [])
	{
		parent::__construct($parameters);
	}
}