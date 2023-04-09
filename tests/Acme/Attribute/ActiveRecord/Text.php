<?php

namespace Acme\Attribute\ActiveRecord;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Text implements SchemaAttribute
{
    public function __construct(
        public string|null $size = null,
        public bool $null = false,
        public bool $unique = false,
        public ?string $collate = null,
    ) {
    }
}
