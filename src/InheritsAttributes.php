<?php

namespace olvlvl\ComposerAttributeCollector;

use Attribute;

/**
 * Use this attribute when a class inherits attributes from traits or parents and is ignored by the collector.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class InheritsAttributes
{
}
