# Use case: Symfony

This use case demonstrates the usage of [olvlvl/composer-attribute-collector][] with a
[Symfony](https://symfony.com/) application. The use case is tested with Composer v2.4.0 and the
latest version, running with PHP 8.2 and 8.4.

After requiring `olvlvl/composer-attribute-collector`, executing `php test.php` should yield an
output as follows, or throw an exception if the collection didn't succeed.

```php
/app/test.php:20:
array(1) {
  [0] =>
  class olvlvl\ComposerAttributeCollector\TargetClass#6 (2) {
    public object $attribute =>
    class Symfony\Component\HttpKernel\Attribute\AsController#5 (0) {
    }
    public string $name =>
    string(30) "App\Controller\HelloController"
  }
}
/app/test.php:37:
array(1) {
  [0] =>
  class olvlvl\ComposerAttributeCollector\TargetMethod#10 (3) {
    public object $attribute =>
    class Symfony\Component\Routing\Attribute\Route#9 (13) {
      private ?string $path =>
      string(6) "/hello"
      private array $localizedPaths =>
      array(0) {
      }
      private array $methods =>
      array(1) {
        ...
      }
      private array $schemes =>
      array(0) {
      }
      private array $aliases =>
      array(0) {
      }
      private ?string $name =>
      string(5) "hello"
      private array $requirements =>
      array(0) {
      }
      private array $options =>
      array(0) {
      }
      private array $defaults =>
      array(0) {
      }
      private ?string $host =>
      NULL
      private ?string $condition =>
      NULL
      private ?int $priority =>
      NULL
      private ?string $env =>
      NULL
    }
    public string $class =>
    string(30) "App\Controller\HelloController"
    public string $name =>
    string(5) "index"
  }
}
```

[olvlvl/composer-attribute-collector]: https://github.com/olvlvl/composer-attribute-collector
