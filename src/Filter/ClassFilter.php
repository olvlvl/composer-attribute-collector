<?php

/*
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace olvlvl\ComposerAttributeCollector\Filter;

use Composer\IO\IOInterface;
use olvlvl\ComposerAttributeCollector\Filter;
use Throwable;

use function interface_exists;

/**
 * Filters classes—removes interfaces and traits.
 */
final class ClassFilter implements Filter
{
    public function filter(string $filepath, string $class, IOInterface $io): bool
    {
        try {
            if (!class_exists($class)) {
                return false;
            }
        } catch (Throwable $e) {
            $io->warning("Discarding '$class' because an error occurred during loading: {$e->getMessage()}");

            return false;
        }

        return true;
    }
}
