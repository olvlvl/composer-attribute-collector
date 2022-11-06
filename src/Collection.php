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
    // @phpstan-ignore-next-line
    public static function __set_state(array $args): object
    {
        return new self($args['targetClasses'], $args['targetMethods']);
    }

    /**
     * @param array<class-string, TargetClassRaw[]> $targetClasses
     * @param array<class-string, TargetMethodRaw[]> $targetMethods
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
            fn(TargetClassRaw $t) => $t->toTarget($attribute),
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
            fn(TargetMethodRaw $t) => $t->toTarget($attribute),
            $this->targetMethods[$attribute] ?? []
        );
    }
}
