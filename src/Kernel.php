<?php

namespace Devdot\Cli;

use Devdot\Cli\Container\ContainerBuilder;
use Devdot\Cli\Container\ServiceProviderTrait;
use Devdot\Cli\Contracts\ContainerInterface;
use Devdot\Cli\Contracts\KernelInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

abstract class Kernel implements KernelInterface, CompilerPassInterface
{
    use ServiceProviderTrait;

    const CACHED_CONTAINER_NAME = 'ProductionContainer';

    private static self $instance;

    private bool $development = false;

    private ContainerInterface $container;

    /**
     * Define your own custom service providers that will
     * @var class-string<Container\ServiceProvider>[]
     */
    protected array $providers = [];

    /**
     * Create a new application Kernel. Make sure to always provide the projects directory and namespace to the parent method!
     */
    public function __construct(
        private string $dir = __DIR__,
        private string $namespace = __NAMESPACE__,
    ) {
    }

    public static function getInstance(): self
    {
        return self::$instance ??= new static();
    }

    /**
     * Run the application. Call this method in the application binary.
     * @param bool $development If false, a cached container is assumed.
     */
    public static function run(bool $development = false): void
    {
        $kernel = static::getInstance();
        $kernel->development = $development;
        /** @var Application */
        $application = $kernel->getContainer()->get(Application::class);
        $code = $application->run();
        exit($code);
    }


    /**
     * Cache the container for use in production. Call this method from the build binary.
     */
    public static function cacheContainer(bool $development = false): void
    {
        $kernel = static::getInstance();
        $kernel->development = $development;

        $kernel->buildFreshContainer()->writeToCache();
    }

    public function isDevelopment(): bool
    {
        return $this->development;
    }

    /**
     * Get the application name.
     * @return string Name of the application. If not overwritten, this will default to the project directory name.
     */
    public function getName(): string
    {
        $root = realpath($this->dir . '/../') ?: '';
        $basename = basename($root);
        if ($basename !== 'tmp') {
            return $basename;
        } else {
            return basename(dirname($root));
        }
    }

    /**
     * Get the application version.
     * @return string Version of the application. If not overwritten, this will default to the last tag on git.
     */
    public function getVersion(): string
    {
        $out = [];
        exec('cd ' . realpath($this->dir . '/../') . ' && git describe --tags --abbrev=0 2>/dev/null', $out);
        $version = $out[0] ?? '';
        return str_starts_with($version, 'v') ? substr($version, 1) : $version;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getDir(): string
    {
        return $this->dir;
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
        if (!$this->development) {
            // we are not in dev, simply load the cached container
            /** @var class-string<\Devdot\Cli\Container\CachedContainer> */
            $class = $this->namespace . '\\' . self::CACHED_CONTAINER_NAME;
            $this->container = new $class();
            $this->container->set('kernel', $this);
        } else {
            $this->buildFreshContainer();
        }
    }

    private function buildFreshContainer(): ContainerBuilder
    {
        // TODO: figure out a way to move container-building-related dependencies to require-dev
        $container = ContainerBuilder::boot($this);
        $this->container = $container;
        return $container;
    }

    /**
     * @return class-string<Container\ServiceProvider>[]
     */
    public function getProviders(): array
    {
        return $this->providers;
    }
}
