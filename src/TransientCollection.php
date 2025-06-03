<?php

namespace olvlvl\ComposerAttributeCollector;

/**
 * A collection of attributes used during the collection process.
 *
 * @internal
 */
final class TransientCollection
{
    /**
     * @var array<class-string, iterable<TransientTargetClass>>
     */
    public array $classes = [];

    /**
     * @var array<class-string, iterable<TransientTargetMethod>>
     */
    public array $methods = [];

    /**
     * @var array<class-string, iterable<iterable<TransientTargetMethodParameter>>>
     */
    public array $methodParameters = [];

    /**
     * @var array<class-string, iterable<TransientTargetProperty>>
     *     Where _key_ is a target class.
     */
    public array $properties = [];

    /**
     * @param class-string $class
     * @param iterable<TransientTargetClass> $targets
     *     The target class.
     */
    public function addClassAttributes(string $class, iterable $targets): void
    {
        $this->classes[$class] = $targets;
    }

    /**
     * @param class-string $class
     * @param iterable<TransientTargetMethod> $targets
     *     The target class.
     */
    public function addMethodAttributes(string $class, iterable $targets): void
    {
        $this->methods[$class] = $targets;
    }

    /**
     * @param class-string $class
     * @param iterable<iterable<TransientTargetMethodParameter>> $targets
     *     The target class.
     */
    public function addMethodParameterAttributes(string $class, iterable $targets): void
    {
        $this->methodParameters[$class] = $targets;
    }

    /**
     * @param class-string $class
     * @param iterable<TransientTargetProperty> $targets
     *     The target class.
     */
    public function addTargetProperties(string $class, iterable $targets): void
    {
        $this->properties[$class] = $targets;
    }
}
