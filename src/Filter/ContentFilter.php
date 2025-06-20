<?php

namespace olvlvl\ComposerAttributeCollector\Filter;

use olvlvl\ComposerAttributeCollector\Filter;
use olvlvl\ComposerAttributeCollector\Logger;

use function assert;
use function file_get_contents;
use function is_string;
use function preg_match;
use function str_contains;

final class ContentFilter implements Filter
{
    public function filter(string $filepath, string $class, Logger $log): bool
    {
        $content = file_get_contents($filepath);

        assert(is_string($content));

        // No hint of attribute usage.
        if (!str_contains($content, '#[')) {
            return false;
        }

        // Hint of an attribute class.
        if (preg_match('/#\[\\\?Attribute[\]\(]/', $content)) {
            $log->debug("Discarding '$class' because it looks like an attribute");
            return false;
        }

        return true;
    }
}
