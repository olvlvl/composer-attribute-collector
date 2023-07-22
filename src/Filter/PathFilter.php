<?php

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
     * @param string[] $include
     * @param string[] $exclude
     */
    public function __construct(
        private array $include,
        private array $exclude,
    ) {
    }

    public function filter(string $filepath, string $class, IOInterface $io): bool
    {
        foreach ($this->exclude as $match) {
            if (str_starts_with($filepath, $match)) {
                $io->debug("Discarding '$class' because its path matches '$match'");

                return false;
            }
        }

        foreach ($this->include as $match) {
            if (str_starts_with($filepath, $match)) {

                return true;
            }
        }

        $io->debug("Discarding '$class' because it does not match any included path");

        return false;
    }
}
