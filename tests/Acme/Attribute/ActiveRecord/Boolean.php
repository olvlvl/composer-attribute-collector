<?php

namespace Acme\Attribute\ActiveRecord;

use Attribute;

#[Attribute]
final class Boolean extends Column
{
    public function __construct(
        bool $null = false,
        bool $unique = false,
    ) {
        parent::__construct(
            type: self::TYPE_INT,
            size: 1,
            null: $null,
            unique: $unique,
        );
    }
}
