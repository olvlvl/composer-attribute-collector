<?php

namespace tests\olvlvl\ComposerAttributeCollector;

use DirectoryIterator;

use function file_exists;
use function realpath;
use function str_starts_with;
use function unlink;

require_once __DIR__ . '/../vendor/autoload.php';

/*
 * Clean up cache
 */

function clear_directory(string $dir): void {
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

clear_directory(__DIR__ . '/../.composer-attribute-collector');
