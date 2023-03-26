<?php

namespace Acme\Attribute\ActiveRecord;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Serial extends Column
{
    public function __construct(
        bool $primary = false,
    ) {
        parent::__construct(
            type: self::TYPE_INT,
            size: self::SIZE_BIG,
            unsigned: true,
            auto_increment: true,
            unique: !$primary,
            primary: $primary,
        );
    }
}
