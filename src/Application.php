<?php

namespace Devdot\Cli;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;

final class Application extends BaseApplication
{
    public function __construct(
        string $name,
        string $version,
        CommandLoaderInterface $commandLoader,
        public readonly bool $development,
    ) {
        parent::__construct($name, $version);
        $this->setAutoExit(false);
        $this->setCommandLoader($commandLoader);
    }
}
