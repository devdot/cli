<?php

namespace Devdot\Cli\Contracts;

interface KernelInterface
{
    public function __construct(
        string $dir = __DIR__,
        string $namespace = __NAMESPACE__,
    );
}
