# Use case: Laravel

Demonstrates the usage of [olvlvl/composer-attribute-collector][] with a
[Laravel](https://laravel.com/) application. The use case is tested with Composer v2.4.0 and the
latest version, running with PHP 8.2 and 8.4.

After requiring `olvlvl/composer-attribute-collector`, executing `php test.php` should yield an
output as follows, or throw an exception if the collection didn't succeed.

```php
/app/test.php:15:
array(2) {
  [0] =>
  class olvlvl\ComposerAttributeCollector\TargetClass#6 (2) {
    public object $attribute =>
    class App\Attributes\SampleAttribute#5 (0) {
    }
    public string $name =>
    string(15) "App\Models\User"
  }
  [1] =>
  class olvlvl\ComposerAttributeCollector\TargetClass#8 (2) {
    public object $attribute =>
    class App\Attributes\SampleAttribute#7 (0) {
    }
    public string $name =>
    string(32) "App\Providers\AppServiceProvider"
  }
}
```



[olvlvl/composer-attribute-collector]: https://github.com/olvlvl/composer-attribute-collector
