<?php

namespace Acme\Attribute\ActiveRecord;

use Attribute;

/**
 * @readonly
 */
#[Attribute]
final class Integer extends Column
{
    public function __construct(
        int|string|null $size = null,
        bool $unsigned = false,
        bool $null = false,
        bool $unique = false,
    ) {
        parent::__construct(
            type: self::TYPE_INT,
            size: $size,
            unsigned: $unsigned,
            null: $null,
            unique: $unique,
        );
    }
}
