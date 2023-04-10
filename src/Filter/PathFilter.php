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

use function str_starts_with;

/**
 * @internal
 */
final class PathFilter implements Filter
{
    /**
     * @param string[] $matches
     */
    public function __construct(
        private array $matches
    ) {
    }

    public function filter(string $filepath, string $class, IOInterface $io): bool
    {
        foreach ($this->matches as $match) {
            if (str_starts_with($filepath, $match)) {
                $io->debug("Discarding '$class' because its path matches '$match'");

                return false;
            }
        }

        return true;
    }
}
