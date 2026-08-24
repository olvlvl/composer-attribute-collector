<?php

namespace olvlvl\ComposerAttributeCollector;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * An attribute collection that uses references and reflection to provide instances.
 *
 * @internal
 */
final class ReferenceCollection implements Collection
{
    /**
     * @param array<class-string, array<array{ class-string }>> $targetClasses
     *     Where _key_ is an attribute class and _value_ an array of arrays
     *     where 0 is a target class.
     * @param array<class-string, array<array{ class-string, non-empty-string }>> $targetMethods
     *     Where _key_ is an attribute class and _value_ an array of arrays
     *     where 0 is a target class and 1 is the target method.
     * @param array<class-string, array<array{ class-string, non-empty-string }>> $targetProperties
     *     Where _key_ is an attribute class and _value_ an array of arrays
     *     where 0 is a target class and 1 is the target property.
     * @param array<class-string, array<array{ class-string, non-empty-string, non-empty-string }>> $targetParameters
     *     Where _key_ is an attribute class and _value_ an array of arrays
     *     where 0 is a target class, 1 is the target method, and 2 is the target parameter.
     */
    public function __construct(
        private array $targetClasses,
        private array $targetMethods,
        private array $targetProperties,
        private array $targetParameters,
    ) {
    }

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function findTargetClasses(string $attribute): array
    {
        $targets = [];

        foreach ($this->targetClasses[$attribute] ?? [] as [$class]) {
            foreach (self::instances((new ReflectionClass($class))->getAttributes($attribute)) as $instance) {
                $targets[] = new TargetClass($instance, $class);
            }
        }

        return $targets;
    }

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function findTargetMethods(string $attribute): array
    {
        $targets = [];

        foreach ($this->targetMethods[$attribute] ?? [] as [$class, $method]) {
            foreach (
                self::instances(
                    (new ReflectionClass($class))->getMethod($method)->getAttributes($attribute)
                ) as $instance
            ) {
                $targets[] = new TargetMethod($instance, $class, $method);
            }
        }

        return $targets;
    }

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function findTargetParameters(string $attribute): array
    {
        $targets = [];

        foreach ($this->targetParameters[$attribute] ?? [] as [$class, $method, $parameter]) {
            foreach ((new ReflectionMethod($class, $method))->getParameters() as $p) {
                if ($p->name !== $parameter) {
                    continue;
                }

                foreach (self::instances($p->getAttributes($attribute)) as $instance) {
                    $targets[] = new TargetParameter($instance, $class, $method, $parameter);
                }
            }
        }

        return $targets;
    }

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function findTargetProperties(string $attribute): array
    {
        $targets = [];

        foreach ($this->targetProperties[$attribute] ?? [] as [$class, $property]) {
            foreach (
                self::instances(
                    (new ReflectionClass($class))->getProperty($property)->getAttributes($attribute)
                ) as $instance
            ) {
                $targets[] = new TargetProperty($instance, $class, $property);
            }
        }

        return $targets;
    }

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function filterTargetClasses(callable $predicate): array
    {
        $ar = [];

        foreach ($this->targetClasses as $attribute => $references) {
            foreach ($references as [$class]) {
                if (!$predicate($attribute, $class)) {
                    continue;
                }

                foreach (self::instances((new ReflectionClass($class))->getAttributes($attribute)) as $instance) {
                    $ar[] = new TargetClass($instance, $class);
                }
            }
        }

        return $ar;
    }

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function filterTargetMethods(callable $predicate): array
    {
        $ar = [];

        foreach ($this->targetMethods as $attribute => $references) {
            foreach ($references as [$class, $method]) {
                if (!$predicate($attribute, $class, $method)) {
                    continue;
                }

                foreach (
                    self::instances(
                        (new ReflectionClass($class))->getMethod($method)->getAttributes($attribute)
                    ) as $instance
                ) {
                    $ar[] = new TargetMethod($instance, $class, $method);
                }
            }
        }

        return $ar;
    }

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function filterTargetParameters(callable $predicate): array
    {
        $ar = [];

        foreach ($this->targetParameters as $attribute => $references) {
            foreach ($references as [$class, $method, $parameter]) {
                if (!$predicate($attribute, $class, $method, $parameter)) {
                    continue;
                }

                foreach ((new ReflectionMethod($class, $method))->getParameters() as $p) {
                    if ($p->name !== $parameter) {
                        continue;
                    }

                    foreach (self::instances($p->getAttributes($attribute)) as $instance) {
                        $ar[] = new TargetParameter($instance, $class, $method, $parameter);
                    }
                }
            }
        }

        return $ar;
    }

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function filterTargetProperties(callable $predicate): array
    {
        $ar = [];

        foreach ($this->targetProperties as $attribute => $references) {
            foreach ($references as [$class, $property]) {
                if (!$predicate($attribute, $class, $property)) {
                    continue;
                }

                foreach (
                    self::instances(
                        (new ReflectionClass($class))->getProperty($property)->getAttributes($attribute)
                    ) as $instance
                ) {
                    $ar[] = new TargetProperty($instance, $class, $property);
                }
            }
        }

        return $ar;
    }

    /**
     * @template T of object
     *
     * @param iterable<ReflectionAttribute<T>> $attributes
     *
     * @return iterable<T>
     */
    private static function instances(iterable $attributes): iterable
    {
        foreach ($attributes as $attribute) {
            yield $attribute->newInstance();
        }
    }
}
