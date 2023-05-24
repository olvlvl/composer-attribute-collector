<?php

declare(strict_types=1);

namespace olvlvl\ComposerAttributeCollector;

final class TransientClass
{
    /**
     * @param array<TransientTargetClass> $classAttributes
     * @param array<TransientTargetMethod> $methodAttributes
     * @param array<TransientTargetProperty> $propertyAttributes
     */
    public function __construct(
        public array $classAttributes,
        public array $methodAttributes,
        public array $propertyAttributes,
    )
    {
    }
}
