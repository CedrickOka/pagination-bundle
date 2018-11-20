<?php

namespace Oka\PaginationBundle;

use Oka\PaginationBundle\DependencyInjection\Compiler\ObjectManagerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OkaPaginationBundle extends Bundle
{
	public function build(ContainerBuilder $container)
	{
		parent::build($container);
		
		$container->addCompilerPass(new ObjectManagerPass());
	}
}
