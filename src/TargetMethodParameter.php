<?php

namespace olvlvl\ComposerAttributeCollector;

/**
 * @readonly
 *
 * @template T of object
 */
final class TargetMethodParameter
{
    /**
     * @var T
     */
    public object $attribute;
    /**
     * @var class-string
     */
    public string $class;
    /**
     * @var non-empty-string
     */
    public string $method;
    /**
     * @var non-empty-string
     */
    public string $name;
    /**
     * @param T $attribute
     * @param class-string $class
     *     The name of the target class.
     * @param non-empty-string $name
     *     The name of the target parameter.
     * @param non-empty-string $method
     *      The name of the target method.
     */
    public function __construct(object $attribute, string $class, string $name, string $method)
    {
        $this->attribute = $attribute;
        $this->class = $class;
        $this->name = $name;
        $this->method = $method;
    }
}
