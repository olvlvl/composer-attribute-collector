<?php

/*
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
     * @param array<class-string, array<array{ mixed[], class-string, string }>> $targetMethods
     *     Where _key_ is an attribute class and _value_ an array of arrays
     *     where 0 are the attribute arguments, 1 is a target class, and 2 is the target method.
     */
    public function __construct(
        private array $targetClasses,
        private array $targetMethods,
    ) {
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $attribute
     *
     * @return TargetClass<T>[]
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
     * @return TargetMethod<T>[]
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
     * @param class-string $class
     *
     * @return ForClass
     */
    public function forClass(string $class): ForClass
    {
        $classAttributes = [];

        foreach ($this->targetClasses as $attribute => $references) {
            foreach ($references as [ $arguments, $targetClass ]) {
                if ($targetClass != $class) {
                    continue;
                }

                $classAttributes[] = self::createClassAttribute($attribute, $arguments, $class);
            }
        }

        $methodAttributes = [];

        foreach ($this->targetMethods as $attribute => $references) {
            foreach ($references as [ $arguments, $targetClass, $targetMethod ]) {
                if ($targetClass != $class) {
                    continue;
                }

                $methodAttributes[$targetMethod][] = self::createMethodAttribute(
                    $attribute,
                    $arguments,
                    $class,
                    $targetMethod
                );
            }
        }

        return new ForClass($classAttributes, $methodAttributes);
    }
}
