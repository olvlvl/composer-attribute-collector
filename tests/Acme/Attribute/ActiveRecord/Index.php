<?php

namespace Acme\Attribute\ActiveRecord;

use Attribute;

/**
 * An index on one or multiple columns.
 *
 * @readonly
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class Index implements SchemaAttribute
{
    /**
     * @param string|array<string> $columns
     *     Identifiers of the columns making the unique index.
     */
    public function __construct(
        public array|string $columns,
        public bool $unique = false,
        public ?string $name = null
    ) {
    }
}
