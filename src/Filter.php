<?php

namespace olvlvl\ComposerAttributeCollector;

/**
 * @internal
 */
interface Filter
{
    /**
     * @param class-string $class
     */
    public function filter(string $filepath, string $class, Logger $log): bool;
}
