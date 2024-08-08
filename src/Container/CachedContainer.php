<?php

namespace Devdot\Cli\Container;

use Devdot\Cli\Contracts\ContainerInterface;
use Symfony\Component\DependencyInjection\Container as SymfonyContainer;

abstract class CachedContainer extends SymfonyContainer implements ContainerInterface
{
}
