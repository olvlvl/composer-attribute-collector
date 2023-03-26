<?php

namespace olvlvl\ComposerAttributeCollector;

/**
 * @readonly
 * @internal
 */
final class TransientTargetMethod
{
    /**
     * @param class-string $attribute The attribute class.
     * @param array<int|string, mixed> $arguments The attribute arguments.
     * @param non-empty-string $name The target method.
     */
    public function __construct(
        public string $attribute,
        public array $arguments,
        public string $name,
    ) {
    }
}
