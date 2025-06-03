<?php

namespace olvlvl\ComposerAttributeCollector;

/**
 * @readonly
 * @internal
 */
final class TransientTargetMethodParameter
{
    /**
     * @param class-string $attribute The attribute class.
     * @param array<int|string, mixed> $arguments The attribute arguments.
     * @param non-empty-string $method The target method.
     * @param non-empty-string $name The target parameter.
     */
    public function __construct(
        public string $attribute,
        public array $arguments,
        public string $method,
        public string $name
    ) {
    }
}
