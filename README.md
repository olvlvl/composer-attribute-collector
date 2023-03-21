# composer-attribute-collector

[![Packagist](https://img.shields.io/packagist/v/olvlvl/composer-attribute-collector.svg)](https://packagist.org/packages/olvlvl/composer-attribute-collector)
[![Code Quality](https://img.shields.io/scrutinizer/g/olvlvl/composer-attribute-collector.svg)](https://scrutinizer-ci.com/g/olvlvl/composer-attribute-collector)
[![Code Coverage](https://img.shields.io/coveralls/olvlvl/composer-attribute-collector.svg)](https://coveralls.io/r/olvlvl/composer-attribute-collector)
[![Downloads](https://img.shields.io/packagist/dt/olvlvl/composer-attribute-collector.svg)](https://packagist.org/packages/olvlvl/composer-attribute-collector)

composer-attribute-collector is a plugin for [Composer][]. Its ambition is to provide a convenient
and near zero-cost way to retrieve targets of PHP 8 attributes. In order to do that, after the
autoloader has been dumped, the plugin collects attribute targets and generates a static file.
Later, these targets can be retrieved through a convenient interface.

#### Features

- Zero configuration.
- No reflection in the generated file.
- No impact on performance.
- No dependency (except Composer of course).
- A single interface to get attribute targets.
- A single interface to get class attributes.



#### Usage

The following example demonstrates how targets and their attributes can be retrieved:

```php
<?php

use olvlvl\ComposerAttributeCollector\Attributes;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Routing\Annotation\Route;

require_once 'vendor/autoload.php';
require_once 'vendor/attributes.php'; // <-- the file created by the plugin

// Find the target classes of the AsMessageHandler attribute.
foreach (Attributes::findTargetClasses(AsMessageHandler::class) as $target) {
    // $target->attribute is an instance of the specified attribute
    // with the actual data.
    var_dump($target->name, $target->attribute);
}

// Find the target methods of the Route attribute.
foreach (Attributes::findTargetMethods(Route::class) as $target) {
    var_dump($target->class, $target->name, $target->attribute);
}

// Find attributes for the ArticleController class.
$attributes = Attributes::forClass(ArticleController::class);

var_dump($attributes->classAttributes);
var_dump($attributes->methodsAttributes);
```



#### Installation

```bash
composer require olvlvl/composer-attribute-collector
```

The plugin is currently experimental and its interface subject to change. Also, it only supports
class and method targets. Please [contribute](CONTRIBUTING.md) if you're interested in shaping its
future.



## Frequently Asked Questions

**Do I need to generate an optimized autoloader?**

You don't need to generate an optimized autoloader for this to work. The
plugin uses code similar to Composer to find classes. Anything that works with Composer should work
with the plugin.

**Can I use the plugin during development?**

Yes, you can use the plugin during development, but keep in mind the attributes file is only
generated after the autoloader is dumped. If you modify attributes you'll have to
run `composer dump` to refresh the attributes file.

As a workaround you could have watchers on the directories that contain classes with attributes to
run `XDEBUG_MODE=off composer dump` when you make changes. [PhpStorm offers file watchers][phpstorm-watchers]. You could also use [spatie/file-system-watcher][], it only requires PHP.



## Autoloading

You can require the attributes file as shown in the usage example, but it's preferable to use Composer's autoloading feature:

```json
{
  "autoloading": {
    "files": [
      "vendor/attributes.php"
    ]
  }
}
```



## Configuration

### Ignoring paths ([root-only][])

composer-attribute-collector inspects files that participate in the autoload process. This can cause
issues with files that have side effects. For instance, `symfony/cache` is known to cause issues, so
we're excluding paths matching `symfony/cache/Traits` from inspection. Additional paths can be
specified using the `extra` section of `composer.json`:

```json
{
  "extra": {
    "composer-attribute-collector": {
      "ignore-paths": [
        "path/to/ignore"
      ]
    }
  }
}
```

For instance, if you are only concerned about the attributes of your own project, you could ignore the vendor directory:

```json
{
  "extra": {
    "composer-attribute-collector": {
      "ignore-paths": [
        "vendor"
      ]
    }
  }
}
```



## Test drive with a Symfony app

You can try the plugin with a fresh installation of Symfony.

Use the `symfony` command to create a new project. If you don't have it yet, you can [download it](https://symfony.com/download).

```bash
symfony new --webapp my_project
```

Now get into that project and install the plugin. You'll be asked if you trust the plugin and wish
to activate it. If you wish to continue, choose `y`.

```bash
cd my_project
composer require olvlvl/composer-attribute-collector
```

The plugin should have generated the file `vendor/attributes.php`. It should look something like
this excerpt:

```php
<?php

// attributes.php @generated by https://github.com/olvlvl/composer-attribute-collector

namespace olvlvl\ComposerAttributeCollector;

Attributes::with(fn () => new Collection(
    targetClasses: [
        \Symfony\Component\Console\Attribute\AsCommand::class => [
            [ ['lint:yaml', 'Lint a YAML file and outputs encountered errors'], \Symfony\Component\Yaml\Command\LintCommand::class ],
            [ ['server:dump', 'Start a dump server that collects and displays dumps in a single place'], \Symfony\Component\VarDumper\Command\ServerDumpCommand::class ],
            [ ['debug:validator', 'Display validation constraints for classes'], \Symfony\Component\Validator\Command\DebugCommand::class ],
            [ ['translation:pull', 'Pull translations from a given provider.'], \Symfony\Component\Translation\Command\TranslationPullCommand::class ],
```

We also have [a repository to test the Symfony usecase](https://github.com/olvlvl/composer-attribute-collector-usecase-symfony).



## Use cases

### Get attributes without using reflection

The method `forClass()` returns the attributes attached to a class, without using reflection. This
can improve the performance of your application if it relies on reflection on hot paths.

```php
// Find attributes for the ArticleController class.
$attributes = Attributes::forClass(ArticleController::class);

var_dump($attributes->classAttributes);
var_dump($attributes->methodsAttributes);
```



### A simpler way to configure your Dependency Injection Container

composer-attribute-collector can help simplify DIC (Dependency Injection Container) configuration.
Long error-prone YAML can be completely replaced with attributes and a compiler pass to use them.
You can still support both YAML and attributes, the "attribute" compiler pass would just configure
the services and tag them automatically.

For example, the package [ICanBoogie/MessageBus][] offers [PHP 8 attributes as an alternative to YAML](https://github.com/ICanBoogie/MessageBus#using-php-8-attributes-instead-of-yaml).

```yaml
services:
  Acme\MenuService\Application\MessageBus\CreateMenuHandler:
    tags:
    - name: message_bus.handler
      message: Acme\MenuService\Application\MessageBus\CreateMenu
    - name: message_bus.permission
      permission: is_admin
    - name: message_bus.permission
      permission: can_write_menu

  Acme\MenuService\Application\MessageBus\DeleteMenuHandler:
    tags:
    - name: message_bus.handler
      message: Acme\MenuService\Application\MessageBus\DeleteMenu
    - name: message_bus.permission
      permission: is_admin
    - name: message_bus.permission
      permission: can_manage_menu

  Acme\MenuService\Presentation\Security\Voters\IsAdmin:
      tags:
      - name: message_bus.voter
        permission: is_admin

  Acme\MenuService\Presentation\Security\Voters\CanWriteMenu:
      tags:
      - name: message_bus.voter
        permission: can_write_menu

  Acme\MenuService\Presentation\Security\Voters\CanManageMenu:
      tags:
      - name: message_bus.voter
        permission: can_manage_menu
```

```php
<?php

// ...

final class Permissions
{
    public const IS_ADMIN = 'is_admin';
    public const CAN_WRITE_MENU = 'can_write_menu';
    public const CAN_MANAGE_MENU = 'can_manage_menu';
}

// ...

use ICanBoogie\MessageBus\Attribute\Permission;

#[Permission(Permissions::IS_ADMIN)]
#[Permission(Permissions::CAN_WRITE_MENU)]
final class CreateMenu
{
    public function __construct(
        public readonly array $payload
    )// ...
}

// ...

use ICanBoogie\MessageBus\Attribute\Handler;

#[Handler]
final class CreateMenuHandler
{
    public function __invoke(CreateMenu $message)// ...
}

// ...

use ICanBoogie\MessageBus\Attribute\Vote;

#[Vote(Permissions::IS_ADMIN)]
final class IsAdmin implements Voter
{
    // ...
}
```



----------



## Continuous Integration

The project is continuously tested by [GitHub actions](https://github.com/olvlvl/composer-attribute-collector/actions).

[![Tests](https://github.com/olvlvl/composer-attribute-collector/workflows/test/badge.svg?branch=main)](https://github.com/olvlvl/composer-attribute-collector/actions?query=workflow%3Atest)
[![Static Analysis](https://github.com/olvlvl/composer-attribute-collector/workflows/static-analysis/badge.svg?branch=main)](https://github.com/olvlvl/composer-attribute-collector/actions?query=workflow%3Astatic-analysis)
[![Code Style](https://github.com/olvlvl/composer-attribute-collector/workflows/code-style/badge.svg?branch=main)](https://github.com/olvlvl/composer-attribute-collector/actions?query=workflow%3Acode-style)



## Code of Conduct

This project adheres to a [Contributor Code of Conduct](CODE_OF_CONDUCT.md). By participating in
this project and its community, you are expected to uphold this code.



## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.



## License

**olvlvl/composer-attribute-collector** is released under the [BSD-3-Clause](LICENSE).



[Composer]:  https://getcomposer.org/
[root-only]: https://getcomposer.org/doc/04-schema.md#root-package
[ICanBoogie/MessageBus]: https://github.com/ICanBoogie/MessageBus
[spatie/file-system-watcher]: https://github.com/spatie/file-system-watcher
[phpstorm-watchers]: https://www.jetbrains.com/help/phpstorm/using-file-watchers.html
