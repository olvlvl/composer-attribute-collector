# composer-attribute-collector

[![Packagist](https://img.shields.io/packagist/v/olvlvl/composer-attribute-collector.svg)](https://packagist.org/packages/olvlvl/composer-attribute-collector)
[![Code Quality](https://img.shields.io/scrutinizer/g/olvlvl/composer-attribute-collector.svg)](https://scrutinizer-ci.com/g/olvlvl/composer-attribute-collector)
[![Code Coverage](https://img.shields.io/coveralls/olvlvl/composer-attribute-collector.svg)](https://coveralls.io/r/olvlvl/composer-attribute-collector)
[![Downloads](https://img.shields.io/packagist/dt/olvlvl/composer-attribute-collector.svg)](https://packagist.org/packages/olvlvl/composer-attribute-collector)

composer-attribute-collector is a plugin for [Composer][]. Its ambition is to provide a convenient
and performant way to retrieve targets of PHP 8 attributes. In order to do that, post autoload
dump, the plugin collects attribute targets and generates a static file. Later, these targets can be
retrieved through a convenient interface.

The following example demonstrates how targets and their attributes can be retrieved:

```php
<?php

use olvlvl\ComposerAttributeCollector\Attributes;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Routing\Annotation\Route;

require_once 'vendor/autoload.php';
require_once 'vendor/attributes.php'; // <-- the file created by the plugin

foreach (Attributes::findTargetClasses(AsMessageHandler::class) as $target) {
    // $target->attribute is an instance of the specified attribute
    // with the actual data.
    var_dump($target->name, $target->attribute);
}

foreach (Attributes::findTargetMethods(Route::class) as $target) {
    var_dump($target->class, $target->name, $target->attribute);
}
```

Features:

- Zero configuration.
- No impact on performance.
- A single way to get attribute targets.

The plugin is currently experimental and its interface subject to change. Currently, it only supports class and method targets. If you're interested in shaping its future, you are invited to [contribute](CONTRIBUTING.md).



#### Installation

```bash
composer require olvlvl/composer-attribute-collector:dev-main
```



## Use cases

### A simpler way to configure your DIC

composer-attribute-collector can help simplify DIC configuration. Long error-prone YAML can be
completely replaced with attributes and a compiler pass to use them. You can still support both
YAML and attributes, the "attribute" compiler pass would just configure the services and tag
them automatically.

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



[Composer]: https://getcomposer.org/
