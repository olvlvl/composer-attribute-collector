<?php

namespace tests\olvlvl\ComposerAttributeCollector;

use olvlvl\ComposerAttributeCollector\Config;
use olvlvl\ComposerAttributeCollector\Plugin;

/**
 * Test the pluging with {@link Config::STRATEGY_EMBEDDED}.
 */
final class PluginEmbeddedTest extends TestAbstract
{
    #[\Override]
    protected static function getStrategy(): string
    {
        return Config::STRATEGY_EMBEDDED;
    }

    #[\Override]
    protected static function dump(Config $config): void
    {
        Plugin::dump($config);
    }
}
