<?php

namespace olvlvl\ComposerAttributeCollector;

/**
 * @template T of object
 */
final readonly class TargetParameter
{
    /**
     * @param T $attribute
     * @param class-string $class
     *     The name of the target class.
     * @param non-empty-string $method
     *      The name of the target method.
     * @param non-empty-string $name
     *     The name of the target parameter.
     */
    public function __construct(
        public object $attribute,
        public string $class,
        public string $method,
        public string $name,
    ) {
    }
}
