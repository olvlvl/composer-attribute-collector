# Migration

## v1.2 to v2.0

### New Requirements

None

### New features

- The plugin now collects attributes on properties. `Attributes::findTargetProperties()` returns target properties, and `filterTargetProperties()` filters properties with a predicate.

### Backward Incompatible Changes

- The value of `ignore-paths` is no longer a pattern, it is now relative to the `composer.json` file. The `{vendor}` placeholder can be used as a placeholder for the absolute path to the vendor directory.

### Deprecated Features

None

### Other Changes

None



## v1.1 to v1.2

### New Requirements

None

### New features

- [#11](https://github.com/olvlvl/composer-attribute-collector/pull/11) Attribute instantiation errors are decorated to help find origin (@withinboredom @olvlvl)
- [#12](https://github.com/olvlvl/composer-attribute-collector/pull/12) `Attributes::filterTargetClasses()` can filter target classes using a predicate (@olvlvl)
- [#12](https://github.com/olvlvl/composer-attribute-collector/pull/12) `Attributes::filterTargetMethods()` can filter target methods using a predicate. `Attributes::predicateForAttributeInstanceOf()` can be used to create a predicate to filter classes or methods targeted by an attribute class or subclass (@olvlvl)
- [#10](https://github.com/olvlvl/composer-attribute-collector/pull/10) 3 types of cache speed up generation by limiting updates to changed files (@xepozz @olvlvl)

### Backward Incompatible Changes

None

### Deprecated Features

None

### Other Changes

None



## v1.0 to v1.1

### New Requirements

None

### New features

- File paths matching `symfony/cache/Traits` are ignored.
- The option `extra.composer-attribute-collection.ignore-paths` can be used to ignore paths.

### Backward Incompatible Changes

None

### Deprecated Features

None

### Other Changes

None

<!--

## vX.x to vX.x

### New Requirements

None

### New features

None

### Backward Incompatible Changes

None

### Deprecated Features

None

### Other Changes

None

-->
