<?php

namespace olvlvl\ComposerAttributeCollector;

use RuntimeException;
use Throwable;

use function array_map;

/**
 * @internal
 */
final class Collection
{
    /**
     * @param array<class-string, array<array{ mixed[], class-string }>> $targetClasses
     *     Where _key_ is an attribute class and _value_ an array of arrays
     *     where 0 are the attribute arguments and 1 is a target class.
     * @param array<class-string, array<array{ mixed[], class-string, non-empty-string }>> $targetMethods
     *     Where _key_ is an attribute class and _value_ an array of arrays
     *     where 0 are the attribute arguments, 1 is a target class, and 2 is the target method.
     * @param array<class-string, array<array{ mixed[], class-string, non-empty-string }>> $targetProperties
     *     Where _key_ is an attribute class and _value_ an array of arrays
     *     where 0 are the attribute arguments, 1 is a target class, and 2 is the target property.
     */
    public function __construct(
        private array $targetClasses,
        private array $targetMethods,
        private array $targetProperties,
    ) {
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $attribute
     *
     * @return array<TargetClass<T>>
     */
    public function findTargetClasses(string $attribute): array
    {
        return array_map(
            fn(array $a) => new TargetClass(self::createClassAttribute($attribute, ...$a), $a[1]),
            $this->targetClasses[$attribute] ?? []
        );
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $attribute
     * @param array<int|string, mixed> $arguments
     * @param class-string $class
     *
     * @return T
     */
    private static function createClassAttribute(string $attribute, array $arguments, string $class): object
    {
        try {
            return new $attribute(...$arguments);
        } catch (Throwable $e) {
            throw new RuntimeException(
                "An error occurred while instantiating attribute $attribute on class $class",
                previous: $e
            );
        }
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $attribute
     *
     * @return array<TargetMethod<T>>
     */
    public function findTargetMethods(string $attribute): array
    {
        return array_map(
            fn(array $a) => new TargetMethod(self::createMethodAttribute($attribute, ...$a), $a[1], $a[2]),
            $this->targetMethods[$attribute] ?? []
        );
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $attribute
     * @param array<int|string, mixed> $arguments
     * @param class-string $class
     *
     * @return T
     */
    private static function createMethodAttribute(
        string $attribute,
        array $arguments,
        string $class,
        string $method
    ): object {
        try {
            return new $attribute(...$arguments);
        } catch (Throwable $e) {
            throw new RuntimeException(
                "An error occurred while instantiating attribute $attribute on method $class::$method",
                previous: $e
            );
        }
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $attribute
     *
     * @return array<TargetProperty<T>>
     */
    public function findTargetProperties(string $attribute): array
    {
        return array_map(
            fn(array $a) => new TargetProperty(self::createPropertyAttribute($attribute, ...$a), $a[1], $a[2]),
            $this->targetProperties[$attribute] ?? []
        );
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $attribute
     * @param array<int|string, mixed> $arguments
     * @param class-string $class
     *
     * @return T
     */
    private static function createPropertyAttribute(
        string $attribute,
        array $arguments,
        string $class,
        string $property
    ): object {
        try {
            return new $attribute(...$arguments);
        } catch (Throwable $e) {
            throw new RuntimeException(
                "An error occurred while instantiating attribute $attribute on property $class::$property",
                previous: $e
            );
        }
    }

    /**
     * @param callable(class-string $attribute, class-string $class):bool $predicate
     *
     * @return array<TargetClass<object>>
     */
    public function filterTargetClasses(callable $predicate): array
    {
        $ar = [];

        foreach ($this->targetClasses as $attribute => $references) {
            foreach ($references as [ $arguments, $class ]) {
                if ($predicate($attribute, $class)) {
                    $ar[] = new TargetClass(self::createClassAttribute($attribute, $arguments, $class), $class);
                }
            }
        }

        return $ar;
    }

    /**
     * @param callable(class-string $attribute, class-string $class, non-empty-string $method):bool $predicate
     *
     * @return array<TargetMethod<object>>
     */
    public function filterTargetMethods(callable $predicate): array
    {
        $ar = [];

        foreach ($this->targetMethods as $attribute => $references) {
            foreach ($references as [ $arguments, $class, $method ]) {
                if ($predicate($attribute, $class, $method)) {
                    $ar[] = new TargetMethod(self::createMethodAttribute(
                        $attribute,
                        $arguments,
                        $class,
                        $method
                    ), $class, $method);
                }
            }
        }

        return $ar;
    }

    /**
     * @param callable(class-string $attribute, class-string $class, non-empty-string $property):bool $predicate
     *
     * @return array<TargetProperty<object>>
     */
    public function filterTargetProperties(callable $predicate): array
    {
        $ar = [];

        foreach ($this->targetProperties as $attribute => $references) {
            foreach ($references as [ $arguments, $class, $property ]) {
                if ($predicate($attribute, $class, $property)) {
                    $ar[] = new TargetProperty(self::createPropertyAttribute(
                        $attribute,
                        $arguments,
                        $class,
                        $property
                    ), $class, $property);
                }
            }
        }

        return $ar;
    }

    /**
     * @param class-string $class
     */
    public function forClass(string $class): ForClass
    {
        $classAttributes = [];

        foreach ($this->filterTargetClasses(fn($a, $c): bool => $c === $class) as $targetClass) {
            $classAttributes[] = $targetClass->attribute;
        }

        $methodAttributes = [];

        foreach ($this->filterTargetMethods(fn($a, $c): bool => $c === $class) as $targetMethod) {
            $methodAttributes[$targetMethod->name][] = $targetMethod->attribute;
        }

        $propertyAttributes = [];

        foreach ($this->filterTargetProperties(fn($a, $c): bool => $c === $class) as $targetProperty) {
            $propertyAttributes[$targetProperty->name][] = $targetProperty->attribute;
        }

        return new ForClass(
            $classAttributes,
            $methodAttributes,
            $propertyAttributes,
        );
    }
}
