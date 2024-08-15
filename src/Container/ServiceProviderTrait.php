<?php

namespace Devdot\Cli\Container;

use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyContainerBuilder;

trait ServiceProviderTrait
{
    /**
     * These services will be automatically registered into the container.
     * @var class-string[]
     */
    protected array $services = [];

    final public function process(SymfonyContainerBuilder $container): void
    {
        $this->booting($container);
    }

    /**
     * Called during the container build phase. Use this to register any complex services or
     * If you overwrite this method, make sure to call parent::booting.
     */
    protected function booting(SymfonyContainerBuilder $container): void
    {
        $this->addServicesToContainer($container);
    }

    /**
     * Registers self::$services into the container. Unless overwritten, this method is always called by ServiceProvider::booting
     */
    final protected function addServicesToContainer(SymfonyContainerBuilder $container, bool $public = false): void
    {
        foreach ($this->services as $key => $class) {
            if (is_string($key)) {
                $container->autowire($key, $class)->setPublic($public);
                $container->setAlias($class, $key)->setPublic($public);
            } else {
                $container->autowire($class, $class)->setPublic($public);
            }
        }
    }
}
