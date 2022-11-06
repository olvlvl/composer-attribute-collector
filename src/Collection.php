<?php

/*
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace olvlvl\ComposerAttributeCollector;

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
            fn(array $a) => new TargetClass(new $attribute(...$a[0]), $a[1]),
            $this->targetClasses[$attribute] ?? []
        );
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
            fn(array $a) => new TargetMethod(new $attribute(...$a[0]), $a[1], $a[2]),
            $this->targetMethods[$attribute] ?? []
        );
    }
}
