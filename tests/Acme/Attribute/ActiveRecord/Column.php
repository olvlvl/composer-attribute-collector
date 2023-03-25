<?php

namespace Acme\Attribute\ActiveRecord;

use Attribute;

/**
 * @readonly
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Column
{
    public const TYPE_BLOB = 'BLOB';
    public const TYPE_BOOLEAN = 'BOOLEAN';
    public const TYPE_CHAR = 'CHAR';
    public const TYPE_DATE = 'DATE';
    public const TYPE_DATETIME = 'DATETIME';
    public const TYPE_FLOAT = 'FLOAT';
    public const TYPE_INT = 'INT';
    public const TYPE_TIMESTAMP = 'TIMESTAMP';
    public const TYPE_TEXT = 'TEXT';
    public const TYPE_VARCHAR = 'VARCHAR';

    public const SIZE_TINY = 'TINY';
    public const SIZE_SMALL = 'SMALL';
    public const SIZE_MEDIUM = 'MEDIUM';
    public const SIZE_BIG = 'BIG';

    public const NOW = 'NOW';
    public const CURRENT_TIMESTAMP = 'CURRENT_TIMESTAMP';

    public function __construct(
        public string $type,
        public string|int|null $size = null,
        public bool $unsigned = false,
        public bool $null = false,
        public mixed $default = null,
        public bool $auto_increment = false,
        public bool $unique = false,
        public bool $primary = false,
        public ?string $comment = null,
        public ?string $collate = null,
    ) {
    }
}
