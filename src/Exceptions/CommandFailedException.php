<?php

namespace Devdot\Cli\Exceptions;

use Devdot\Cli\Command;
use Exception;

class CommandFailedException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message, Command::FAILURE);
    }
}
