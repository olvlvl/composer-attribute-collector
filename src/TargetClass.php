<?php

namespace olvlvl\ComposerAttributeCollector;

/**
 * @readonly
 *
 * @template T of object
 */
final class TargetClass
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
