<?php

namespace olvlvl\ComposerAttributeCollector\Filter;

use olvlvl\ComposerAttributeCollector\Filter;
use olvlvl\ComposerAttributeCollector\Logger;
use Throwable;

use function interface_exists;

final class InterfaceFilter implements Filter
{
    public function filter(string $filepath, string $class, Logger $log): bool
    {
        try {
            if (interface_exists($class)) {
                return false;
            }
        } catch (Throwable $e) {
            $log->warning("Discarding '$class' because an error occurred during loading: {$e->getMessage()}");

            return false;
        }

        return true;
    }
}
