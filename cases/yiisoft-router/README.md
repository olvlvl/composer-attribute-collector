# Use case: YiiSoft Router

This use case demonstrates the usage of [olvlvl/composer-attribute-collector][] with the
[YiiSoft Router](https://github.com/yiisoft/router) and
[yiisoft/router-composer-attribute-collector][].

The router's attribute collector uses `Route`, `Group`, and method-level attributes such as
`Get`, `Post`, `Put`, `Delete`, `Patch`, `Head`, and `Options` that implement
`RouteAttributeInterface`.

After requiring `olvlvl/composer-attribute-collector`, executing `php test.php` should
yield output similar to the following, or throw an exception if the collection didn't
succeed.

```
array(1) {
  [0] =>
  class Yiisoft\Router\Group#7 (8) {
    private array $routes =>
    array(2) {
      [0] =>
      class Yiisoft\Router\Route#15 (10) {
        ...
      }
      [1] =>
      class Yiisoft\Router\Route#14 (10) {
        ...
      }
    }
    private readonly ?string $prefix =>
    string(5) "/blog"
    ...
  }
}
```

[olvlvl/composer-attribute-collector]: https://github.com/olvlvl/composer-attribute-collector
[yiisoft/router-composer-attribute-collector]: https://github.com/yiisoft/router-composer-attribute-collector
