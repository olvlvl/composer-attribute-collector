<?php

/*
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace olvlvl\ComposerAttributeCollector;

/**
 * Collects classes and methods with attributes.
 *
 * @internal
 */
final class Collector
{
    /**
     * @var array<class-string, TargetClassRaw[]>
     */
    public array $classes = [];

    /**
     * @var array<class-string, TargetMethodRaw[]>
     */
    public array $methods = [];

    /**
     * @param array<array{ class-string, array<int|string, mixed> }> $attributes
     *     An array of method attributes, where `0` is an attribute class, `1` the attributes arguments.
     * @param class-string $class
     *     The target class.
     */
    public function addClassAttributes(array $attributes, string $class): void
    {
        foreach ($attributes as [ $attribute, $arguments ]) {
            $this->classes[$attribute][] = new TargetClassRaw($arguments, $class);
        }
    }

    /**
     * @param array<array{ class-string, array<int|string, mixed>, string }> $attributes
     *     An array of method attributes, where `0` is an attribute class, `1` the attributes arguments,
     *     and `2` the method.
     * @param class-string $class
     *     The target class.
     */
    public function addMethodAttributes(array $attributes, string $class): void
    {
        foreach ($attributes as [ $attribute, $arguments, $method ]) {
            $this->methods[$attribute][] = new TargetMethodRaw($arguments, $class, $method);
        }
    }
}
