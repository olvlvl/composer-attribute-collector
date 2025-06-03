<?php

namespace olvlvl\ComposerAttributeCollector;

/**
 * @readonly
 * @internal
 */
final class TransientTargetMethodParameter
{
    /**
     * @var class-string
     */
    public string $attribute;
    /**
     * @var array<int|string, mixed>
     */
    public array $arguments;
    /**
     * @var non-empty-string
     */
    public string $name;
    /**
     * @var non-empty-string
     */
    public string $method;
    /**
     * @param class-string $attribute The attribute class.
     * @param array<int|string, mixed> $arguments The attribute arguments.
     * @param non-empty-string $method The target method.
     * @param non-empty-string $name The target parameter.
     */
    public function __construct(string $attribute, array $arguments, string $method, string $name)
    {
        $this->attribute = $attribute;
        $this->arguments = $arguments;
        $this->method = $method;
        $this->name = $name;
    }
}
