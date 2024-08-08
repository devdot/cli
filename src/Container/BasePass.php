<?php

namespace Devdot\Cli\Container;

use Devdot\Cli\Application;
use Devdot\Cli\Contracts\ContainerInterface;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BasePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // register self
        $container->setAlias(ContainerInterface::class, 'service_container');
        $container->setAlias(\Psr\Container\ContainerInterface::class, 'service_container');

        // register application
        $container->autowire('application', Application::class)->setPublic(true)->setArguments([
            '$name' => '%application_name%',
            '$version' => '%application_version%',
        ]);
        $container->setAlias(Application::class, 'application')->setPublic(true);

        // add the command loader
        $container->autowire(CommandLoaderInterface::class, ContainerCommandLoader::class)->setArguments([
            '$commandMap' => '%commands_as_map%',
        ]);
    }
}
