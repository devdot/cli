<?php

namespace Devdot\Cli\Traits;

use Devdot\Cli\Command;
use Devdot\Cli\Exceptions\RunProcessException;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Process\Process;

/**
 * @mixin \Devdot\Cli\Command
 */
trait RunProcessTrait
{
    private Process $lastProcess;

    private ?string $runProcessDefaultCwd = null;
    private bool $runProcessThrowErrors = false;

    protected function runProcess(string|array $command, bool $quiet = false, ?string $cwd = null, ?array $env = null, ?int $timeout = 60): int
    {
        if (is_string($command)) {
            // break up the command
            $input = new StringInput($command);
            $command = $input->getRawTokens();
        }

        $cwd ??= $this->runProcessDefaultCwd;

        $this->lastProcess = new Process($command, $cwd, $env, null, $timeout);

        if (!$quiet) {
            $getcwd = getcwd();
            $dir = realpath($cwd ?? $getcwd);

            if (str_starts_with($dir, $getcwd)) {
                $dir = './' . substr($dir, strlen($getcwd));
            }

            $this->output->writeln(sprintf('%s> %s', $dir, implode(' ', $command)));
        }

        $interactive = !$quiet && $this->input->isInteractive();
        if ($interactive) {
            $this->lastProcess->setTty(true);
        }

        $code = $this->lastProcess->run();

        if (!$interactive && !$quiet) {
            $this->output->write($this->lastProcess->getOutput());
            $this->output->write($this->lastProcess->getErrorOutput());
        }

        if ($code !== Command::SUCCESS && $this->runProcessThrowErrors) {
            throw RunProcessException::fromProcess($this->lastProcess);
        }

        return $code;
    }

    protected function getLastProcess(): Process
    {
        return $this->lastProcess;
    }

    protected function setRunProcessDefaultCwd(?string $cwd): void
    {
        $this->runProcessDefaultCwd = $cwd;
    }

    protected function setRunProcessThrowErrors(bool $throw): void
    {
        $this->runProcessThrowErrors = $throw;
    }
}
