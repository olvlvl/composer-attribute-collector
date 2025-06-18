<?php

namespace Acme81\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class SampleNested
{
    public function __construct(
        public SampleNestedValue $value,
    ) {
    }
}
