<?php

namespace Devdot\Cli\Contracts;

use Psr\Container\ContainerInterface as SymfonyContainerInterface;

interface ContainerInterface extends SymfonyContainerInterface
{
    public function set(string $id, ?object $service): void;
}
