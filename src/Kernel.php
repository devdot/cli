<?php

namespace Devdot\Cli;

use Devdot\Cli\Container\ContainerBuilder;
use Devdot\Cli\Contracts\ContainerInterface;
use Devdot\Cli\Contracts\KernelInterface;

class Kernel implements KernelInterface
{
    private static self $instance;

    private ContainerInterface $container;

    public function __construct(
        private string $dir = __DIR__,
        private string $namespace = __NAMESPACE__,
    ) {
    }

    public static function getInstance(): self
    {
        return self::$instance ??= new static();
    }

    public static function run(): void
    {
        /** @var Application */
        $application = static::getInstance()->getContainer()->get(Application::class);
        $application->run();
    }

    public function getName(): string
    {
        return basename(realpath($this->dir . '/../') ?: '');
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
