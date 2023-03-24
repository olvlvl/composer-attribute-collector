<?php

namespace olvlvl\ComposerAttributeCollector;

use Composer\IO\IOInterface;

/**
 * @internal
 */
interface Filter
{
    /**
     * @param class-string $class
     */
    public function filter(string $filepath, string $class, IOInterface $io): bool;
}
