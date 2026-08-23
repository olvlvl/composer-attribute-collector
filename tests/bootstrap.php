<?php

namespace tests\olvlvl\ComposerAttributeCollector;

use olvlvl\ComposerAttributeCollector\Plugin;

use function dirname;
use function is_dir;

$autoload = require dirname(__DIR__) . '/vendor/autoload.php';

// Avoid Acme85 cases breaking older versions.
if (PHP_VERSION_ID >= 85000) {
    $autoload->addPsr4("Acme85\\", "tests/Acme85");
}

/**
 * @return non-empty-string
 */
function get_cache_dir(): string
{
    $dir = dirname(__DIR__) . '/' . Plugin::CACHE_DIR;

    if (!is_dir($dir)) {
        mkdir($dir);
    }

    return $dir;
}
