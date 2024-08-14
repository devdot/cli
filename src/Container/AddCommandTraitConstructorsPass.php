<?php

namespace Devdot\Cli\Container;

use Devdot\Cli\Command;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class AddCommandTraitConstructorsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->findTaggedServiceIds('command') as $id => $tags) {
            // search the traits for constructors
            if(is_subclass_of($id, Command::class)) {
                $this->scanTraitsFor($id, $container->getDefinition($id));
            }
        }
    }

    /**
     * @param class-string<Command> $class
     */
    private function scanTraitsFor(string $class, Definition $definition): void
    {
        // search the parent first
        if ($class !== Command::class) {
            /** @var bool|class-string<Command> */
            $parent = get_parent_class($class);
            if (is_string($parent) && class_exists($parent)) {
                $this->scanTraitsFor($parent, $definition);
            }
        }

        // now search lower too
        foreach (class_uses($class) as $trait) {
            $basename = basename(str_replace('\\', '/', $trait));
            $method = '__construct' . $basename;
            if (method_exists($trait, $method)) {
                $definition->addMethodCall($method);
            }
        }
    }
}
