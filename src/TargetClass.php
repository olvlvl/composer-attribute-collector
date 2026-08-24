<?php

namespace olvlvl\ComposerAttributeCollector;

/**
 * @template T of object
 */
final readonly class TargetClass
{
    /**
     * @param T $attribute
     * @param class-string $name
     *     The name of the target class.
     */
    public function __construct(
        public object $attribute,
        public string $name,
    ) {
    }
}
