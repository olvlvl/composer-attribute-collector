<?php

namespace Acme\Attribute\ActiveRecord;

use Attribute;

/**
 * @readonly
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Text extends Column
{
    public function __construct(
        string|null $size = null,
        bool $null = false,
        bool $unique = false,
        bool $primary = false,
        ?string $comment = null,
        ?string $collate = null,
    ) {
        $size = match ($size) {
            self::SIZE_SMALL => null,
            self::SIZE_BIG => 'LONG',
            default => $size,
        };

        parent::__construct(
            type: self::TYPE_TEXT,
            size: $size,
            null: $null,
            unique: $unique,
            primary: $primary,
            comment: $comment,
            collate: $collate,
        );
    }
}
