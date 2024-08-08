<?php

namespace Devdot\Cli;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;

final class Application extends BaseApplication
{
    public function __construct(string $name, string $version, CommandLoaderInterface $commandLoader)
    {
        parent::__construct($name, $version);
        $this->setAutoExit(true);
        $this->setCommandLoader($commandLoader);
    }
}
