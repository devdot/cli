<?php

namespace Devdot\Cli;

use Closure;
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

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct();

        $this->setName($this::getGeneratedName());
    }

    public static function getGeneratedName(): string
    {
        $namespace = static::class;
        $segments = explode('\\', $namespace);

        $name = array_pop($segments);
        while (count($segments) > 0) {
            $segment = array_pop($segments);

            if ($segment === 'Commands') {
                break;
            }

            $name = $segment . ':' . $name;
        }

        return strtolower($name);
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
            return $t->getCode();
        }
    }

    abstract protected function handle(): int;
}
