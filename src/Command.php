<?php

namespace Devdot\Cli;

use Devdot\Cli\Contracts\ContainerInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

abstract class Command extends SymfonyCommand
{
    protected ContainerInterface $container;
    protected InputInterface $input;
    protected OutputInterface $output;
    protected SymfonyStyle $style;

    public function __construct()
    {
        parent::__construct();

        $this->setName($this::getGeneratedName());
    }

    /**
     * Get the generated name of this command. Will be cached by the container, therefore returns should not be dynamic.
     * Overwrite this method if you want to change the commands name.
     * @return string Name the command will be called by.
     */
    public static function getGeneratedName(): string
    {
        $camelCaseToDash = fn(string $camel): string => (preg_replace('/([a-z])([A-Z])/', '$1-$2', $camel) ?? '');

        $namespace = static::class;
        $segments = explode('\\', $namespace);

        $name = $camelCaseToDash(array_pop($segments));
        while (count($segments) > 0) {
            $segment = $camelCaseToDash(array_pop($segments));

            if ($segment === 'Commands') {
                break;
            }

            $name = $segment . ':' . $name;
        }

        return strtolower($name);
    }

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    public function isDevelopment(): bool
    {
        /** @var Application */
        $application = $this->container->get('application');
        return $application->development;
    }

    final protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;
        $this->style = new SymfonyStyle($input, $output);

        try {
            return $this->handle();
        } catch (Throwable $t) {
            $this->style->error($t->getMessage());

            if ($this->isDevelopment()) {
                $this->output->write($t->getTraceAsString());
            }

            return $t->getCode();
        }
    }

    /**
     * Handle execution of the command.
     * @return int Exit code. Recommended use: self::SUCCESS, self::FAILURE or self::INVALID
     */
    abstract protected function handle(): int;
}
