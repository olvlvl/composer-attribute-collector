<?php

namespace olvlvl\ComposerAttributeCollector;

/**
 * @readonly
 * @internal
 */
final class TransientTargetProperty
{
    /**
     * @param class-string $attribute The attribute class.
     * @param array<int|string, mixed> $arguments The attribute arguments.
     * @param non-empty-string $name The target property.
     */
    public function __construct(
        public string $attribute,
        public array $arguments,
        public string $name,
    ) {
    }
}
