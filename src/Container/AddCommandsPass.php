<?php

namespace Devdot\Cli\Container;

use Composer\Autoload\ClassLoader;
use Devdot\Cli\Command;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AddCommandsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $namespace = $container->getParameter('namespace');
        assert(is_string($namespace));
        $commands = $this->getCommandClassNames($namespace, 'Commands');

        foreach ($commands as $command) {
            $container->autowire($command, $command)
                ->setTags(['command' => []])
                ->addMethodCall('setContainer')
            ;
        }
    }

    /**
     * @return class-string<Command>[]
     */
    private function getCommandClassNames(string $rootNamespace, string $subPath): array
    {
        $paths = null;

        foreach (ClassLoader::getRegisteredLoaders() as $loader) {
            $paths = $loader->getPrefixesPsr4()[$rootNamespace . '\\'] ?? null;

            if ($paths !== null) {
                break;
            }
        }

        $paths = array_map(fn(string $path): string => $path . '/' . $subPath, $paths ?? []);
        $paths = array_map('realpath', $paths);
        $paths = array_filter($paths, fn(bool|string $path): bool => is_string($path) && is_dir($path));

        $classes = array_merge(...array_map(fn(string $path): array => $this->getClassNamesFromDirectory($path), $paths));
        $classes = array_map(fn(string $class): string => $rootNamespace . '\\' . $subPath . '\\' . $class, $classes);

        $commands = array_filter($classes, fn(string $class): bool => is_a($class, Command::class, true));
        $commands = array_filter($commands, fn(string $class): bool => !(new ReflectionClass($class))->isAbstract());
        return $commands;
    }

    /**
     * @return string[]
     */
    private function getClassNamesFromDirectory(string $path, string $relativePath = '', bool $recursive = true): array
    {
        $classes = [];

        foreach (scandir($path) ?: [] as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $path . '/' . $file;
            if (is_dir($filePath)) {
                if ($recursive) {
                    $subClasses = $this->getClassNamesFromDirectory($filePath);
                    $classes = array_merge($classes, array_map(fn(string $class): string => $file . '\\' . $class, $subClasses));
                }
            } else {
                $class = $file;
                if (str_ends_with($class, '.php')) {
                    $class = substr($file, 0, -4);
                }

                $classes[] = $class;
            }
        }

        return $classes;
    }
}
