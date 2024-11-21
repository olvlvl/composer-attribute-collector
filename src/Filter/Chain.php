<?php

namespace olvlvl\ComposerAttributeCollector\Filter;

use Composer\IO\IOInterface;
use olvlvl\ComposerAttributeCollector\Filter;

final class Chain implements Filter
{
    /**
     * @param iterable<Filter> $filters
     */
    public function __construct(
        private iterable $filters
    ) {
    }

    public function filter(string $filepath, string $class, IOInterface $io): bool
    {
        foreach ($this->filters as $filter) {
            if ($filter->filter($filepath, $class, $io) === false) {
                return false;
            }
        }

        return true;
    }
}
