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

final class IgnorePathFilter implements Filter
{
    /**
     * @param string[] $paths
     */
    public function __construct(
        private string $basePath,
        private array  $paths,
    ) {
    }

    public function filter(string $filepath, string $class, IOInterface $io): bool
    {
        foreach ($this->paths as $ignoredPath) {
            $basePath = str_starts_with($ignoredPath, '/') ? $ignoredPath : "{$this->basePath}/$ignoredPath";
            if (str_starts_with($filepath, $basePath)) {
                $io->debug("Discarding '$class' because its path matches '$ignoredPath'");

                return false;
            }
        }

        return true;
    }
}
