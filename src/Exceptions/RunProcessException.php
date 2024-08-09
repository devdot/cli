<?php

namespace Devdot\Cli\Exceptions;

use Exception;
use Symfony\Component\Process\Process;

class RunProcessException extends Exception
{
    public function __construct(string $error, int $code)
    {
        parent::__construct($error, $code);
    }

    public static function fromProcess(Process $process): self
    {
        return new self($process->getErrorOutput(), $process->getExitCode());
    }
}
