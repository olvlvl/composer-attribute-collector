<?php

namespace olvlvl\ComposerAttributeCollector\Factory;

use olvlvl\ComposerAttributeCollector\Filter;
use olvlvl\ComposerAttributeCollector\Filter\Chain;
use olvlvl\ComposerAttributeCollector\Filter\ClassFilter;
use olvlvl\ComposerAttributeCollector\Filter\ContentFilter;

/**
 * @internal
 */
final class FilterFactory
{
    public static function create(): Filter
    {
        return new Chain([
            new ContentFilter(),
            new ClassFilter(),
        ]);
    }
}
