<?php

namespace Devdot\Cli\Container;

use Devdot\Cli\Contracts\ContainerInterface;
use Devdot\Cli\Kernel;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;

class ContainerBuilder extends SymfonyContainerBuilder implements ContainerInterface
{
    public static function boot(Kernel $kernel): self
    {
        $container = new self();

        $container->setParameter('development', $kernel->isDevelopment());
        $container->setParameter('namespace', $kernel->getNamespace());
        $container->setParameter('application_name', $kernel->getName());
        $container->setParameter('application_version', $kernel->getVersion());

        $kernel->configureContainer($container);

        $container->addCompilerPass(new BasePass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 100);
        $container->addCompilerPass(new AddCommandsPass());
        $container->addCompilerPass(new LoadCommandsPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, -1);
        $container->addCompilerPass(new AddCommandTraitConstructorsPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, -1);

        $container->register(Kernel::class, Kernel::class)->setSynthetic(true)->setPublic(true);

        $container->compile();

        $container->set(Kernel::class, $kernel);

        return $container;
    }

    public function writeToCache(): void
    {
        $dumper = new PhpDumper($this);
        /** @var Kernel */
        $kernel = $this->get(Kernel::class);

        file_put_contents($kernel->getDir() . '/' . $kernel::CACHED_CONTAINER_NAME . '.php', $dumper->dump([
            'class' => $kernel::CACHED_CONTAINER_NAME,
            'base_class' => '\\' . CachedContainer::class,
            'namespace' => $kernel->getNamespace(),
        ]));
    }
}
