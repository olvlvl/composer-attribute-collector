<?php

namespace olvlvl\ComposerAttributeCollector;

/**
 * @readonly
 *
 * @template T of object
 */
final class TargetProperty
{
    /**
     * @param T $attribute
     * @param class-string $class
     *     The name of the target class.
     * @param non-empty-string $name
     *     The name of the target property.
     */
    public function __construct(
        public object $attribute,
        public string $class,
        public string $name,
    ) {
    }
}
