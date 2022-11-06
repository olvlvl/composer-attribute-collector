<?php

/*
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace olvlvl\ComposerAttributeCollector;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

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
    private array $classes = [];

    /**
     * @var array<class-string, TargetMethodRaw[]>
     */
    private array $methods = [];

    /**
     * @param ReflectionAttribute<object> $attribute
     * @param ReflectionClass<object> $class
     */
    public function addTargetClass(ReflectionAttribute $attribute, ReflectionClass $class): void
    {
        $this->classes[$attribute->getName()][]
            = new TargetClassRaw($attribute->getArguments(), $class->name);
    }

    /**
     * @param ReflectionAttribute<object> $attribute
     */
    public function addTargetMethod(ReflectionAttribute $attribute, ReflectionMethod $method): void
    {
        $this->methods[$attribute->getName()][]
            = new TargetMethodRaw($attribute->getArguments(), $method->class, $method->name);
    }

    public function collect(): Collection
    {
        return new Collection(
            $this->classes,
            $this->methods,
        );
    }
}
