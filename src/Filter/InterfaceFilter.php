<?php

namespace olvlvl\ComposerAttributeCollector\Filter;

use Composer\IO\IOInterface;
use olvlvl\ComposerAttributeCollector\Filter;
use Throwable;

use function interface_exists;

final class InterfaceFilter implements Filter
{
    public function filter(string $filepath, string $class, IOInterface $io): bool
    {
        try {
            if (interface_exists($class)) {
                return false;
            }
        } catch (Throwable $e) {
            $io->warning("Discarding '$class' because an error occurred during loading: {$e->getMessage()}");

            return false;
        }

        return true;
    }
}
