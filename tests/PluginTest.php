<?php

namespace tests\olvlvl\ComposerAttributeCollector;

use olvlvl\ComposerAttributeCollector\Config;
use olvlvl\ComposerAttributeCollector\Plugin;

final class PluginTest extends CollectorTestAbstract
{
    protected static function dump(Config $config): void
    {
        Plugin::dump($config);
    }
}
