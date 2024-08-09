<?php

namespace Devdot\Cli\Container;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

abstract class ServiceProvider implements CompilerPassInterface
{
    use ServiceProviderTrait;
}
