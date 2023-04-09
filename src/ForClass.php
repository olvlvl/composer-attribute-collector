<?php

namespace olvlvl\ComposerAttributeCollector;

/**
 * @readonly
 */
final class ForClass
{
    /**
     * @param iterable<object> $classAttributes
     *     Where _value_ is an attribute.
     * @param array<string, iterable<object>> $methodsAttributes
     *     Where _key_ is a method and _value_ and iterable where _value_ is an attribute.
     * @param array<string, iterable<object>> $propertyAttributes
     *     Where _key_ is a property and _value_ and iterable where _value_ is an attribute.
     */
    public function __construct(
        public iterable $classAttributes,
        public array $methodsAttributes,
        public array $propertyAttributes,
    ) {
    }
}
