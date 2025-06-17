<?php

namespace olvlvl\ComposerAttributeCollector\Filter;

use olvlvl\ComposerAttributeCollector\Filter;
use olvlvl\ComposerAttributeCollector\Logger;
use Throwable;

use function class_exists;
use function interface_exists;
use function trait_exists;

final class ClassFilter implements Filter
{
    public function filter(string $filepath, string $class, Logger $log): bool
    {
        try {
            return class_exists($class) || interface_exists($class) || trait_exists($class);
        } catch (Throwable $e) {
            $log->warning("Discarding '$class' because an error occurred during loading: {$e->getMessage()}");

            return false;
        }
    }
}
