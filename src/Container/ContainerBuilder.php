<?php

namespace Devdot\Cli\Container;

use Devdot\Cli\Contracts\ContainerInterface;
use Devdot\Cli\Kernel;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyContainerBuilder;

class ContainerBuilder extends SymfonyContainerBuilder implements ContainerInterface
{
    public static function boot(Kernel $kernel): self
    {
        $container = new self();

        $container->setParameter('namespace', $kernel->getNamespace());
        $container->setParameter('application_name', $kernel->getName());
        $container->setParameter('application_version', $kernel->getVersion());

        $kernel->configureContainer($container);

        $container->addCompilerPass(new BasePass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 100);
        $container->addCompilerPass(new AddCommandsPass());
        $container->addCompilerPass(new LoadCommandsPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, -1);

        $container->compile();

        return $container;
    }
}
