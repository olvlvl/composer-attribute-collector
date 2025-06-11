<?php

namespace olvlvl\ComposerAttributeCollector\Filter;

use olvlvl\ComposerAttributeCollector\Filter;
use olvlvl\ComposerAttributeCollector\Logger;

final class Chain implements Filter
{
    /**
     * @param iterable<Filter> $filters
     */
    public function __construct(
        private iterable $filters
    ) {
    }

    public function filter(string $filepath, string $class, Logger $log): bool
    {
        foreach ($this->filters as $filter) {
            if ($filter->filter($filepath, $class, $log) === false) {
                return false;
            }
        }

        return true;
    }
}
