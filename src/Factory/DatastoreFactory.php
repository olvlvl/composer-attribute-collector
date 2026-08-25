<?php

namespace olvlvl\ComposerAttributeCollector\Factory;

use olvlvl\ComposerAttributeCollector\Config;
use olvlvl\ComposerAttributeCollector\Datastore;
use olvlvl\ComposerAttributeCollector\Datastore\FileDatastore;
use olvlvl\ComposerAttributeCollector\Datastore\RuntimeDatastore;
use olvlvl\ComposerAttributeCollector\Logger;
use olvlvl\ComposerAttributeCollector\Plugin;
use RuntimeException;

use function getcwd;

use const DIRECTORY_SEPARATOR;

/**
 * @internal
 */
final class DatastoreFactory
{
    /**
     * Returns the datastore to use according to the configuration.
     */
    public static function from(Config $config, Logger $log): Datastore
    {
        if (!$config->useCache) {
            return new RuntimeDatastore();
        }

        $basePath = getcwd() ?: throw new RuntimeException('Unable to locate base path');

        return new FileDatastore($basePath . DIRECTORY_SEPARATOR . Plugin::CACHE_DIR, $log);
    }
}
