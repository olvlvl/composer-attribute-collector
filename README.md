# composer-attribute-collector

[![Packagist](https://img.shields.io/packagist/v/olvlvl/composer-attribute-collector.svg)](https://packagist.org/packages/olvlvl/composer-attribute-collector)
[![Code Quality](https://img.shields.io/scrutinizer/g/olvlvl/composer-attribute-collector.svg)](https://scrutinizer-ci.com/g/olvlvl/composer-attribute-collector)
[![Code Coverage](https://img.shields.io/coveralls/olvlvl/composer-attribute-collector.svg)](https://coveralls.io/r/olvlvl/composer-attribute-collector)
[![Downloads](https://img.shields.io/packagist/dt/olvlvl/composer-attribute-collector.svg)](https://packagist.org/packages/olvlvl/composer-attribute-collector)

composer-attribute-collector is a plugin for [Composer][]. Its ambition is to provide a convenient
and near zero-cost way to retrieve targets of PHP 8 attributes. After the autoloader has been
dumped, the plugin collects attribute targets and generates a static file. Later, these targets can
be retrieved through a convenient interface, without involving reflexion.



#### Features

- Little configuration
- No reflection in the generated file
- No impact on performance
- No dependency (except Composer of course)
- A single interface to get attribute targets: classes, methods, and properties
- Can cache discoveries to speed up consecutive runs.



#### Usage

The following example demonstrates how targets and their attributes can be retrieved:

```php
<?php

use olvlvl\ComposerAttributeCollector\Attributes;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\Mapping\Column;

require_once 'vendor/autoload.php';
require_once 'vendor/attributes.php'; // <-- the file created by the plugin

// Find the target classes of the AsMessageHandler attribute.
foreach (Attributes::findTargetClasses(AsMessageHandler::class) as $target) {
    // $target->attribute is an instance of the specified attribute
    // with the actual data.
    var_dump($target->attribute, $target->name);
}

// Find the target methods of the Route attribute.
foreach (Attributes::findTargetMethods(Route::class) as $target) {
    var_dump($target->attribute, $target->class, $target->name);
}

// Find the target properties of the Column attribute.
foreach (Attributes::findTargetProperties(Column::class) as $target) {
    var_dump($target->attribute, $target->class, $target->name);
}

// Filter target methods using a predicate.
// This is also available for classes and properties.
foreach (Attributes::filterTargetMethods(
    fn($attribute) => is_a($attribute, Route::class, true)
) as $target) {
    var_dump($target->attribute, $target->class, $target->name);
}

// Find class, method, and property attributes for the ArticleController class.
$attributes = Attributes::forClass(ArticleController::class);

var_dump($attributes->classAttributes);
var_dump($attributes->methodsAttributes);
var_dump($attributes->propertyAttributes);
```



## Getting started


### Installation

```shell
composer require olvlvl/composer-attribute-collector
```

The plugin is currently experimental and its interface subject to change. At the moment, it only
supports class, method, and property targets. Please [contribute](CONTRIBUTING.md) if you're interested in
shaping its future.



### Sample configuration

The plugin only inspects paths and files specified in the configuration with the direction
`include`. That's usually your "src" directory. Add this section to your `composer.json` file to
enable the generation of the attributes file on autoload dump.

Check the [Configuration options](#configuration) for more details.

```json
{
  "extra": {
    "composer-attribute-collector": {
      "include": [
        "src"
      ]
    }
  }
}
```



### Autoloading

You can require the attributes file using `require_once 'vendor/attributes.php';` but you might
prefer using Composer's autoloading feature:

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

### Including paths or files ([root-only][])

Use the `include` property to define the paths or files to inspect for attributes. Without this
property, the attributes file will be empty.

The specified paths are relative to the `composer.json` file, and the `{vendor}` placeholder is
replaced with the path to the vendor folder.

```json
{
  "extra": {
    "composer-attribute-collector": {
      "include": [
        "path-or-file/to/include"
      ]
    }
  }
}
```

### Excluding paths or files ([root-only][])

Use the `exclude` property to excluded paths or files from inspection. This is handy when files
cause issues or have side effects.

The specified paths are relative to the `composer.json` file, and the `{vendor}` placeholder is
replaced with the path to the vendor folder.

```json
{
  "extra": {
    "composer-attribute-collector": {
      "exclude": [
        "path-or-file/to/exclude"
      ]
    }
  }
}
```

### Cache discoveries between runs

The plugin is able to maintain a cache to reuse discoveries between runs. To enable the cache,
set the environment variable `COMPOSER_ATTRIBUTE_COLLECTOR_USE_CACHE` to `1`, `yes`, or `true`.
Cache items are persisted in the `.composer-attribute-collector` directory, you might want to add
it to your `.gitignore` file.



## Frequently Asked Questions

**Do I need to generate an optimized autoloader?**

You don't need to generate an optimized autoloader for this to work. The plugin uses code similar
to Composer to find classes. Anything that works with Composer should work with the plugin.

**Can I use the plugin during development?**

Yes, you can use the plugin during development, but keep in mind the attributes file is only
generated after the autoloader is dumped. If you modify attributes you'll have to run
`composer dump` to refresh the attributes file.

As a workaround you could have watchers on the directories that contain classes with attributes to
run `XDEBUG_MODE=off composer dump` when you make changes. [PhpStorm offers file watchers][phpstorm-watchers]. You could also use [spatie/file-system-watcher][], it only requires PHP. If the plugin is too slow for your liking,
try running the command with `COMPOSER_ATTRIBUTE_COLLECTOR_USE_CACHE=yes`, it will enable caching
and speed up consecutive runs.



## Test drive with the Symfony Demo

You can try the plugin with a fresh installation of the [Symfony Demo Application](https://github.com/symfony/demo).

After you followed the instruction to install the demo, get into the project's directory and install the plugin. You'll be asked if you trust the plugin and wish to activate it. If you wish to continue, choose `y`.

```shell
composer require olvlvl/composer-attribute-collector
```

Add the `composer-attribute-collector` node to `extra` and the autoload item to the `composer.json` file:

```json
{
  "autoload": {
    "files": [
        "vendor/attributes.php"
    ]
  },
  "extra": {
    "composer-attribute-collector": {
      "include": [
        "src"
      ]
    }
  }
}
```

Now dump the autoload:

```shell
composer dump
```

You should see log messages similar to this:

```
Generating autoload files
Generating attributes file
Generated attributes file in 9.137 ms
Generated autoload files
```

The plugin should have generated the file `vendor/attributes.php`. Let's see if we can get the controller methods tagged as routes. Create a PHP file with the following content and run it:

```php
<?php

use olvlvl\ComposerAttributeCollector\Attributes;
use Symfony\Component\Routing\Annotation\Route;

require_once 'vendor/autoload.php';

$targets = Attributes::filterTargetMethods(
    Attributes::predicateForAttributeInstanceOf(Route::class)
);

foreach ($targets as $target) {
    echo "action: $target->class#$target->name, path: {$target->attribute->getPath()}\n";
}
```

You should see an output similar to the following excerpt:

```
action: App\Controller\BlogController#index, path: /
action: App\Controller\BlogController#index, path: /rss.xml
action: App\Controller\BlogController#index, path: /page/{page<[1-9]\d{0,8}>}
action: App\Controller\BlogController#postShow, path: /posts/{slug}
action: App\Controller\BlogController#commentNew, path: /comment/{postSlug}/new
action: App\Controller\BlogController#search, path: /search
```

The demo application configured with the plugin is [available on GitHub](https://github.com/olvlvl/composer-attribute-collector-usecase-symfony).



## Use cases

### Get attributes without using reflection

The method `forClass()` returns the attributes attached to a class, without using reflection. This
can improve the performance of your application if it relies on reflection on hot paths.

```php
// Find attributes for the ArticleController class.
$attributes = Attributes::forClass(ArticleController::class);

var_dump($attributes->classAttributes);
var_dump($attributes->methodsAttributes);
var_dump($attributes->propertyAttributes);
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



### Configure components from attributes

Using attributes simplifies configuration, placing definition closer to the code, where it's used. ICanBoogie's router can be configured automatically from attributes. The following example demonstrates how the `Route` attribute can be used at the class level to define a prefix for the route attributes such as `Get` that are used to tag actions. Action identifiers can be inferred from the controller class and the method names e.g. `skills:list`.

```php
<?php

// …

#[Route('/skills')]
final class SkillController extends ControllerAbstract
{
    #[Post]
    private function create(): void
    {
        // …
    }

    #[Get('.html')]
    private function list(): void
    {
        // …
    }

    #[Get('/summonable.html')]
    private function summonable(): void
    {
        // …
    }

    #[Get('/learnable.html')]
    private function learnable(): void
    {
        // …
    }

    #[Get('/:slug.html')]
    private function show(string $slug): void
    {
        // …
    }
}
```

Because the `Get` and `Post` attributes extend `Route`, all action methods can be retrieved with the `filterTargetMethods()` method.

```php
/** @var TargetMethod<Route>[] $target_methods */
$target_methods = Attributes::filterTargetMethods(
    Attributes::predicateForAttributeInstanceOf(Route::class)
);
```

Now then, configuring the router looks as simple as this:

```php
<?php

use ICanBoogie\Binding\Routing\ConfigBuilder;

/* @var ConfigBuilder $config */

$config->from_attributes();
```



## Using Attributes

### Filtering target methods

`filterTargetMethods()` can filter target methods using a predicate. This can be helpful when a number of attributes extend another one, and you are interested in collecting any instance of that attribute. The `filerTargetClasses()` and `filterTargetProperties()` methods provide similar feature for classes and properties.

Let's say we have a `Route` attribute extended by `Get`, `Post`, `Put`…

```php
<?php

use olvlvl\ComposerAttributeCollector\Attributes;

/** @var TargetMethod<Route>[] $target_methods */
$target_methods = [
    ...Attributes::findTargetMethods(Get::class),
    ...Attributes::findTargetMethods(Head::class),
    ...Attributes::findTargetMethods(Post::class),
    ...Attributes::findTargetMethods(Put::class),
    ...Attributes::findTargetMethods(Delete::class),
    ...Attributes::findTargetMethods(Connect::class),
    ...Attributes::findTargetMethods(Options::class),
    ...Attributes::findTargetMethods(Trace::class),
    ...Attributes::findTargetMethods(Patch::class),
    ...Attributes::findTargetMethods(Route::class),
];

// Can be replaced by:

/** @var TargetMethod<Route>[] $target_methods */
$target_methods = Attributes::filterTargetMethods(
    Attributes::predicateForAttributeInstanceOf(Route::class)
);
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
