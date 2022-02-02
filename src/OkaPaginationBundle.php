<?php

namespace Oka\PaginationBundle;

use Oka\PaginationBundle\DependencyInjection\Compiler\DoctrineRegistryServiceLocatorPass;
use Oka\PaginationBundle\DependencyInjection\Compiler\FilterExpressionsPass;
use Oka\PaginationBundle\DependencyInjection\Compiler\TwigExtensionPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class OkaPaginationBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new TwigExtensionPass());
        $container->addCompilerPass(new FilterExpressionsPass());
        $container->addCompilerPass(new DoctrineRegistryServiceLocatorPass());
    }
}
