<?php

namespace tests\olvlvl\ComposerAttributeCollector;

use DirectoryIterator;

use function dirname;
use function file_exists;
use function is_dir;
use function realpath;
use function str_starts_with;
use function unlink;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * @return non-empty-string
 */
function get_cache_dir(): string
{
    $dir = dirname(__DIR__) . '/.composer-attribute-collector';

    if (!is_dir($dir)) {
        mkdir($dir);
    }

    return $dir;
}

/**
 * Clean up cache
 *
 * @param non-empty-string $dir
 */
function clear_directory(string $dir): void
{
    $dir = realpath($dir);

    if ($dir && file_exists($dir)) {
        $di = new DirectoryIterator($dir);

        foreach ($di as $file) {
            /** @var DirectoryIterator $file */

            if ($file->isDot()) {
                continue;
            }

            if (str_starts_with($file->getFilename(), '.')) {
                continue;
            }

            unlink($file->getPathname());
        }
    }
}
