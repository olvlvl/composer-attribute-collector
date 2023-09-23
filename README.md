# composer-attribute-collector

[![Packagist](https://img.shields.io/packagist/v/olvlvl/composer-attribute-collector.svg)](https://packagist.org/packages/olvlvl/composer-attribute-collector)
[![Code Quality](https://img.shields.io/scrutinizer/g/olvlvl/composer-attribute-collector.svg)](https://scrutinizer-ci.com/g/olvlvl/composer-attribute-collector)
[![Code Coverage](https://img.shields.io/coveralls/olvlvl/composer-attribute-collector.svg)](https://coveralls.io/r/olvlvl/composer-attribute-collector)
[![Downloads](https://img.shields.io/packagist/dt/olvlvl/composer-attribute-collector.svg)](https://packagist.org/packages/olvlvl/composer-attribute-collector)

composer-attribute-collector is a plugin for [Composer][]. Its ambition is to provide a convenient
way—and near zero cost—to retrieve targets of PHP 8 attributes. After the autoloader has been
dumped, the plugin collects attribute targets and generates a static file. Later, these targets can
be retrieved through a convenient interface, without reflection. The plugin is useful when you need
to _discover_ attribute targets in a codebase—for known targets you can use reflection.



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
// You can also filter target classes and properties.
$predicate = fn($attribute) => is_a($attribute, Route::class, true);
# or
$predicate = Attributes::predicateForAttributeInstanceOf(Route::class);

foreach (Attributes::filterTargetMethods($predicate) as $target) {
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

Use the `exclude` property to exclude paths or files from inspection. This is handy when files
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



## Test drive with the Symfony Demo

You can try the plugin with a fresh installation of the [Symfony Demo Application](https://github.com/symfony/demo).

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

Use Composer to install the plugin. You'll be asked if you trust the plugin and wish to activate it.
If you wish to continue, choose `y`.

```shell
composer require olvlvl/composer-attribute-collector
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

$predicate = Attributes::predicateForAttributeInstanceOf(Route::class);
$targets = Attributes::filterTargetMethods($predicate);

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



## Frequently Asked Questions

**Do I need to generate an optimized autoloader?**

You don't need to generate an optimized autoloader for this to work. The plugin uses code similar
to Composer to find classes. Anything that works with Composer should work with the plugin.

**Can I use the plugin during development?**

Yes, you can use the plugin during development, but keep in mind the attributes file is only
generated after the autoloader is dumped. If you modify attributes you'll have to run
`composer dump` to refresh the attributes file.

As a workaround you could have watchers on the directories that contain classes with attributes to
run `XDEBUG_MODE=off composer dump` when you make changes. [PhpStorm offers file watchers][phpstorm-watchers].
You could also use [spatie/file-system-watcher][], it only requires PHP. If the plugin is too slow
for your liking, try running the command with `COMPOSER_ATTRIBUTE_COLLECTOR_USE_CACHE=yes`, it will
enable caching and speed up consecutive runs.



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
[spatie/file-system-watcher]: https://github.com/spatie/file-system-watcher
[phpstorm-watchers]: https://www.jetbrains.com/help/phpstorm/using-file-watchers.html
