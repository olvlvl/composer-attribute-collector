<?php

namespace Acme\Attribute\ActiveRecord;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Boolean implements SchemaAttribute
{
    public function __construct(
        public bool $null = false,
    ) {
    }
}
