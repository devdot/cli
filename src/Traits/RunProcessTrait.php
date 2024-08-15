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
    private bool $runProcessShowInternalCommand = false;

    /**
     * @param string|array $command Either a string of the command or an array that is compatible with Symfony Process. You may also call another command by adding it's class name as the first array entry.
     */
    protected function runProcess(string|array $command, bool $quiet = false, ?string $cwd = null, ?array $env = null, ?int $timeout = 60): int
    {
        if (is_array($command) && is_subclass_of($command[0], Command::class)) {
            // we simply run the other command from here now
            $class = array_shift($command);
            $input = new StringInput($class::getGeneratedName() . ' ' . implode(' ', $command));

            if ($this->runProcessShowInternalCommand) {
                $this->output->writeln('./> ' . (string) $input);
            }

            return $this->getApplication()->run($input, $this->output);
        }

        $cwd ??= $this->runProcessDefaultCwd;

        if (is_string($command)) {
            $this->lastProcess = Process::fromShellCommandline($command, $cwd, $env, null, $timeout);
        } else {
            $this->lastProcess = new Process($command, $cwd, $env, null, $timeout);
        }


        if (!$quiet) {
            $getcwd = getcwd();
            $dir = realpath($cwd ?? $getcwd);

            if (str_starts_with($dir, $getcwd)) {
                $sub = substr($dir, strlen($getcwd));
                $dir = (str_starts_with($sub, '/') ? '.' : './') . $sub;
            }

            $this->output->writeln(sprintf('%s> %s', $dir, is_string($command) ? $command : implode(' ', $command)));
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

    protected function setRunProcessShowInternalCommand(bool $show): void
    {
        $this->runProcessShowInternalCommand = $show;
    }
}
