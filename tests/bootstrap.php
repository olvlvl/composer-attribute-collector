<?php

namespace tests\olvlvl\ComposerAttributeCollector;

use olvlvl\ComposerAttributeCollector\Plugin;

use function dirname;
use function is_dir;

require_once dirname(__DIR__) . '/vendor/autoload.php';

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
