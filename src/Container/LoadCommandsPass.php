<?php

namespace Devdot\Cli\Container;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LoadCommandsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $commandsAsMap = [];

        foreach ($container->findTaggedServiceIds('command') as $id => $tags) {
            $definition = $container->getDefinition($id);

            $definition->setPublic(true);
            $definition->setAutowired(true);

            // add to command map for ContainerCommandLoader
            $commandsAsMap[$id::getGeneratedName()] = $id;
        }

        $container->setParameter('commands_as_map', $commandsAsMap);
    }
}
