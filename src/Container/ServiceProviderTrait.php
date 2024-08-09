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

    public function process(SymfonyContainerBuilder $container): void
    {
        $this->booting($container);
    }

    protected function booting(SymfonyContainerBuilder $container): void
    {
        $this->addServicesToContainer($container);
    }

    protected function addServicesToContainer(SymfonyContainerBuilder $container, bool $public = false): void
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
