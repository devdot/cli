<?php

namespace Tests\Traits;

use Devdot\Cli\Command;
use Devdot\Cli\Traits\ForceTrait;

final class ForceTraitCommand extends Command
{
    use ForceTrait;

    #[\Override]
    public function handle(): int
    {
        throw new \Exception('Not implemented');
    }
}
