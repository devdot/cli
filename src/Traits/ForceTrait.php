<?php

namespace Devdot\Cli\Traits;

/**
 * @mixin \Devdot\Cli\Command
 */
trait ForceTrait
{
    public function __constructForceTrait(): void
    {
        $this->addOption('force', 'f', null, 'Force run the command.');
    }
}
