# CHANGELOG

## v2.1.0

### New Requirements

None

### New features

- [#38](https://github.com/olvlvl/composer-attribute-collector/pull/38) Attributes are now collected from interfaces as well as classes. (@olvlvl)

- [#37](https://github.com/olvlvl/composer-attribute-collector/pull/37) Parameter attributes are now collected. Use the method `findTargetParameters()` to find target parameters, and the method `filterTargetParameters()` to filter target parameters according to a predicate. (@staabm @olvlvl)

- [#39](https://github.com/olvlvl/composer-attribute-collector/pull/39) The `InheritsAttributes` attribute can be used on classes that inherit their attributes from traits, properties, or methods, and were previously ignored by the collection process. (@olvlvl)

    ```php
    trait UrlTrait
    {
        #[UrlGetter]
        public function get_url(): string
        {
            return '/url';
        }
    }

    #[InheritsAttributes]
    class InheritedAttributeSample
    {
        use UrlTrait;
    }
    ```

### Deprecated Features

None

### Backward Incompatible Changes

None

### Other Changes

[#35](https://github.com/olvlvl/composer-attribute-collector/pull/35) The collector runs as a
command to avoid clashes between packages used by Composer and those used by the application, such
as incompatible signatures between different versions of the PSR Logger. (@olvlvl)



## v2.0.2

### New Requirements

None

### New features

None

### Deprecated Features

None

### Backward Incompatible Changes

None

### Other Changes

- Fix PHP 8.4 deprecation notice "Implicitly marking parameter * as nullable is deprecated."
- Simplify attribute creation functions.



## v2.0.1

### New Requirements

None

### New features

None

### Backward Incompatible Changes

None

### Deprecated Features

None

### Other Changes

- #26 Fix enum support on PHP < 8.2.0 (@mnavarrocarter)



## v2.0.0

### New Requirements

None

### New features

- The plugin now collects attributes on properties. `Attributes::findTargetProperties()` returns target properties, and `filterTargetProperties()` filters properties with a predicate.

### Deprecated Features

- The `ignore-paths` directive has been replaced by `exclude`.

### Backward Incompatible Changes

- The paths defined by the `include` and `exclude` directives are relative to the `composer.json` file. The `{vendor}` placeholder is replaced by the absolute path to the vendor directory.

### Other Changes

- The plugin no longer uses a file cache by default. To persist a cache between runs, set the environment variable `COMPOSER_ATTRIBUTE_COLLECTOR_USE_CACHE` to `1`, `yes`, or `true`.



## v1.2.0

### New Requirements

None

### New features

- [#11](https://github.com/olvlvl/composer-attribute-collector/pull/11) Attribute instantiation errors are decorated to help find origin (@withinboredom @olvlvl)
- [#12](https://github.com/olvlvl/composer-attribute-collector/pull/12) `Attributes::filterTargetClasses()` can filter target classes using a predicate (@olvlvl)
- [#12](https://github.com/olvlvl/composer-attribute-collector/pull/12) `Attributes::filterTargetMethods()` can filter target methods using a predicate. `Attributes::predicateForAttributeInstanceOf()` can be used to create a predicate to filter classes or methods targeted by an attribute class or subclass (@olvlvl)
- [#10](https://github.com/olvlvl/composer-attribute-collector/pull/10) 3 types of cache speed up generation by limiting updates to changed files (@xepozz @olvlvl)

### Deprecated Features

None

### Backward Incompatible Changes

None

### Other Changes

None



## v1.1.0

### New Requirements

None

### New features

- File paths matching `symfony/cache/Traits` are ignored.
- The option `extra.composer-attribute-collection.ignore-paths` can be used to ignore paths.

### Deprecated Features

None

### Backward Incompatible Changes

None

### Other Changes

None

<!--

## vX.x to vX.x

### New Requirements

None

### New features

None

### Deprecated Features

None

### Backward Incompatible Changes

None

### Other Changes

None

-->
