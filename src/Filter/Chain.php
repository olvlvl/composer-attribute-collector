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
