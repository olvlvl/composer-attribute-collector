<?php

namespace olvlvl\ComposerAttributeCollector;

/**
 * @internal
 */
final readonly class TransientTargetClass
{
    /**
     * @param class-string $attribute The attribute class.
     * @param array<int|string, mixed> $arguments The attribute arguments.
     */
    public function __construct(
        public string $attribute,
        public array $arguments,
    ) {
    }
}
