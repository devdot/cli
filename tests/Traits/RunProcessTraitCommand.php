<?php

namespace Tests\Traits;

use Devdot\Cli\Command;
use Devdot\Cli\Traits\RunProcessTrait;

final class RunProcessTraitCommand extends Command
{
    use RunProcessTrait;

    #[\Override]
    public function handle(): int
    {
        throw new \Exception('Not implemented');
    }
}
