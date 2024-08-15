devdot/cli
==========

*Tools for CLI projects, on top of symfony/console.*

## Features

- [Intuitive application layout](#application-layout)
- [Container / Dependency Injection](#container)
- [File-based command routing by default](#file-based-routing)
- [Building companion utility](#cli-builder)
- [Fast production application](#production-build)
- [Powerful PHP traits for command classes](#traits)

## Installation

Get started with a new project:

```
composer create-project devdot/cli-project
```

Or install the framework into an existing project like this:

```
composer require devdot/cli
composer require devdot/cli-builder --dev
vendor/bin/cli-builder init
```

## Documentation

### CLI Builder

Using [devdot/cli-builder](https://github.com/devdot/cli-builder), cli applications can be built in minutes.

Create new commands like this:

```
vendor/bin/cli-builder make:command Testing/Example
```

This will create a new command class at `src/Commands/Testing/Example` that is wired to `testing:example`. This generated class will be fully registered in you application and it extends your projects's custom base command class:

```php
namespace App\Commands\Testing;

use App\Commands\Command;

class Example extends Command
{
    protected function handle(): int
    {
        //

        return self::SUCCESS;
    }
}
```

Run `vendor/bin/cli-builder list` to see an overview of available commands.

### Application Layout

Take a look at [devdot/cli-project](https://github.com/devdot/cli-project) to see the default application layout.

```
⊢ bin/
    ⊢ build                     // build script for production
    ⊢ dev                       // application in development mode
    ⊢ prod                      // application in production mode
⊢ src/
    ⊢ Commands/
        ⊢ Example/
            ⊢ SendMessage.php   // command "example:send-message"
        ⊢ About.php             // command "about"
        ⊢ Command.php           // project base command class (optional)
    ⊢ Kernel.php                // application kernel, called by binaries
```

#### Binaries

If you want to provide your production application through your composer project package, you may call `cli-builder composer:add-binary` or add it to composer.json yourself, ([see more](https://getcomposer.org/doc/articles/vendor-binaries.md)).

The provided binaries are described as follows:


| Binary      | cli-builder command | Description                                                                                                                 |
| ------------- | --------------------- | ----------------------------------------------------------------------------------------------------------------------------- |
| `bin/dev`   | `run:dev`           | Execute your application in development mode. The container will be rebuild on every call, by default no caching is active. |
| `bin/prod`  | `run:prod`          | Execute your application in production mode. This will assume a built container and use all available caching.              |
| `bin/build` | `run:build`         | Build and cache the production container. This is required for autowiring to work in production.                            |

For more details on the build process, see [Production Build](#production-build).

#### File based routing

Command names are generated automatically based on their location in the `App\\Commands` namespace. See examples for generated names:


| Command                | Path                                   |
| ------------------------ | ---------------------------------------- |
| about                  | src/Commands/About.php                 |
| load-config            | src/Commands/LoadConfig.php            |
| cache:clear            | src/Commands/Cache/Clear.php           |
| app-config:reload-data | src/Commands/AppConfig/ReloadData.php  |
| routing:manager:reset  | src/Commands/Routing/Manager/Reset.php |

Upon building the [container](#container), all classes in the namespace `App\\Commands` that are commands (extending `Devdot\\Cli\\Command`) are added to the CLI application. **No manual registration is necessary**, command names do not need to be set. Constructors and [Trait Constructors](#command-trait-constructors) are autowired with the [container](#container).

If you want to change a commands name, you may do so like this:

```php
class SomeCustomCommand extends Command
{
    public static function getGeneratedName(): string
    {
        return 'custom_command_name';
    }
}


```

If you want to use a new or changed command in the production application, make sure to run `bin/build` first. The [container](#container) will store command names (`Command::getGeneratedName`) and cache them for production.

To increase performance, command classes will only be created when necessary (by using Symfony's [ContainerCommandLoader](https://symfony.com/doc/current/console/lazy_commands.html)). Therefore, `__construct` and `configure` are only called when the command is executed or when the commands are `list`-ed.

#### Exceptions as Exit Codes

Exceptions may be used to find a direct exit from nested control flow. Any uncaught exception during `Command::handle` will cause the process to exit with the exception's error code. The exception message will be displayed (the stack trace is only shown in development).

You may use exceptions as exit codes like this:

```php
namespace App\Commands;

use Devdot\Cli\Exceptions\CommandFailedException;

class ExampleCommand extends Command
{
    protected function handle(): int
    {
        $this->handleReadingFiles();
        $this->handleProcessingFiles()
        $this->handleWritingFiles();

        return self::SUCCESS;
    }

    private function handleReadingFiles(): void
    {
        if ($this->fileReader->get() === '') {
            // exit the command
            throw new CommandFailedException('File is empty'); // the command will return code 1
        }
    }

    // ...
}
```

#### Kernel

The `Kernel.php` file is required as it anchors the container in your namespace and allows auto-detection of commands.

The container is invoked by binaries through `Kernel::run`, with the first param set to `true` on development builds (which will build a fresh container on every call).

You may change the application name and version by overwriting `Kernel::getName` and `Kernel::getVersion` respectively. By default, the name is assumed from the directory name and the version is pulled from the latest git tag.

Services for the container may be added as class-string in the `Kernel::$services` array. For more on services and providers, see [Service Providers](#service-providers).

#### Customization

Follow these steps to change the default namespace or directory:

- change namespace in `composer.json`
- regenerate the Kernel using `cli-builder make:kernel`
- change namespace in entrypoints (files in `bin`)
- dump composer autoload

### Traits

There are some traits that provide common functionality to commands as you require them. Thanks to [Trait Constructors](#command-trait-constructors), they require no setup in `Command::__construct` or `Command::configure`.


| Trait                               | Description                                                                                                                                             |
| ------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `Devdot\Cli\Traits\ForceTrait`      | Adds`--force` (`-f`) option to a command. Check input like this: `$this->input->getOption('force')`.                                                    |
| `Devdot\Cli\Traits\RunProcessTrait` | Add the`Command::runProcess` utility. Execute on the command line or call other internal commands with a nice wrapper. Works on top of symfony/process. |

### Container

All parts of a `devdot/cli` application are connected through the container's dependency injection. The container registers commands and constructs commands, and autowires all services as needed.

Register a service in through the Kernel and have it automatically injected in your command:

```php
final class Kernel extends BaseKernel
{
    protected array $services = [
        // register your service classes here
        \App\Services\YourService::class,
    ];

    // ...
}

class ExampleCommand extends Command
{
    public function __construct(
        private \App\Services\YourService $service, // automatically injected
    ) {
        // ...
    }

    protected function handle(): int
    {
        $this->service->doSomething();

        // ...
    }
}

```

Services are autowired to other services, so you may use dependency injection on service constructors too.

The container is build *before* commands are created. In production mode, the container is only loaded from cache. Therefore, unused services cause no overhead in production. See more at [Production Build](#production-build).

#### Service Providers

If you have services that require complex setup, you need to use a service provider. Create a new service provider using `cli-builder make:provider YourProvider` and add it to `Kernel::$providers`.

```php
// src/Providers/YourProvider.php
namespace App\Providers;

use Devdot\Cli\Container\ServiceProvider;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class YourProvider extends ServiceProvider
{
    /** @var class-string[] */
    protected array $services = [
        // you may add additional service class names here
    ];

    public function booting(ContainerBuilder $container): void
    {
        parent::booting($container); // this will register the services in $this->services

        // this is called during the container build phase

        $container->autowire(SomeService::class)->setFactory([SomeFactory::class, 'make']);
    }
}


// src/Kernel.php
namespace App;

final class Kernel extends Devdot\Cli\Kernel
{
    /** @var class-string<\Devdot\Cli\Container\ServiceProvider>[] */
    protected array $providers = [
        // add the new provider here
        \App\Providers\YourProvider::class,
    ];

    // ...
}
```

In production mode, service providers are never invoked, because the entire container is cached (see more at [Production Build](#production-build)).

The [Kernel](#kernel) acts as a service provider too, so you may overwrite `Kernel::booting` just like in a regular service provider.

#### Command Trait Constructors

Traits that are used in commands may have their own constructors that are called automatically. Trait constructors can be used to setup the command:

```php
namespace Devdot\Cli\Traits;

trait ForceTrait
{
    public function __constructForceTrait(): void
    {
        // this method will be called by the container after Command::__construct on each command that uses this trait
        $this->addOption('force', 'f', null, 'Force run the command.');
    }
}
```

Trait constructors can receive services through dependency injection like this:

```php
trait DataTrait
{
    protected Data $data
    public function __constructForceTrait(Data $data): void
    {
        // $data will be provided by the container if the service Data is registered
        $this->data = $data;
    }
}
```

Trait constructors are called on the commands that implement those traits. Traits may be inherited both by other traits or in the command classes.

#### Production Build

It is recommended to deploy the application based on the production binary. Use `bin/build` to build the container that is used in production.

Some important hints:

- Command names are generated through `Command::getGeneratedName` and cached in the container. Dynamic names will not work in production.
- Application name and version are cached in the container and will not behave dynamically in production.
- Unused services will be optimized out of the production container. Only accessing them without dependency injection (i.e. through `$container->get('service')` may cause them to be unavailable in production.
- Service Providers are only called during the container build process. Therefore, these classes will not even be initialized in production.

By default, the cached container is stored in `src/ProductionContainer.php`. The class can be changed through overwriting `const Kernel::CACHED_CONTAINER_NAME`.

You may use `cli-builder build:phar` to create an executable PHAR from your production application.
