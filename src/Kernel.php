<?php

namespace Devdot\Cli;

use Devdot\Cli\Container\ContainerBuilder;
use Devdot\Cli\Contracts\ContainerInterface;

class Kernel
{
    private static self $instance;

    private ContainerInterface $container;

    public function __construct(
        private string $dir,
        private string $namespace,
    ) {
    }

    public static function getInstance(): static
    {
        return static::$instance ??= new static();
    }

    public static function run(): void
    {
        static::getInstance()->getContainer()->get(Application::class)->run();
    }

    public function getName(): string
    {
        return basename(realpath($this->dir . '/../'));
    }

    public function getVersion(): string
    {
        $out = [];
        exec('cd ' . realpath($this->dir . '/../') . ' && git describe --tags --abbrev=0 2>/dev/null', $out);
        return $out[0] ?? '0.0.1';
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getContainer(): ContainerInterface
    {
        if (!isset($this->container)) {
            $this->buildContainer();
        }
        return $this->container;
    }

    private function buildContainer(): void
    {
        $this->container = ContainerBuilder::boot($this);
    }

    public function configureContainer(ContainerBuilder $container): void
    {
    }
}
