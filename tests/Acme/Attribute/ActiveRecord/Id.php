<?php

namespace Acme\Attribute\ActiveRecord;

use Attribute;

/**
 * Marks one or multiple properties that constitute the record identifier i.e. the primary key in the database.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Id implements SchemaAttribute
{
}
