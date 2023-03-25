<?php

namespace Acme\Attribute\ActiveRecord;

use Attribute;

/**
 * @readonly
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Varchar extends Column
{
    public function __construct(
        int $size = 255,
        bool $null = false,
        bool $unique = false,
        bool $primary = false,
        ?string $comment = null,
        ?string $collate = null,
    ) {
        parent::__construct(
            type: self::TYPE_VARCHAR,
            size: $size,
            null: $null,
            unique: $unique,
            primary: $primary,
            comment: $comment,
            collate: $collate,
        );
    }
}
