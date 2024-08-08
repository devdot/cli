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

    /**
     * @var array<Closure(): void>
     */
    private array $configures = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct();

        $this->constructTraits();
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

    private function constructTraits(): void
    {
        // TODO: find a way to cache these
        foreach (class_uses($this) as $trait) {
            $basename = basename(str_replace('\\', '/', $trait));
            $method = 'construct' . $basename;
            if (method_exists($this, $method)) {
                $this->$method(); // TODO: do this with DI
            }
        }
    }

    protected function configure(): void
    {
        $this->setName($this::getGeneratedName());

        foreach ($this->configures as $configure) {
            $configure->call($this);
        }
    }

    public function bindConfigure(Closure $closure): void
    {
        $this->configures[] = $closure;
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
