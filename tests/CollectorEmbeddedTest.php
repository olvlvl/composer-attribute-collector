<?php

namespace tests\olvlvl\ComposerAttributeCollector;

use olvlvl\ComposerAttributeCollector\Collector;
use olvlvl\ComposerAttributeCollector\Config;

/**
 * Test {@link Collector} with {@link Config::STRATEGY_EMBEDDED}.
 */
final class CollectorEmbeddedTest extends TestAbstract
{
    #[\Override]
    protected static function getStrategy(): string
    {
        return Config::STRATEGY_EMBEDDED;
    }

    #[\Override]
    protected static function dump(Config $config): void
    {
        $collector = new Collector($config, new FakeLogger());
        $collector->run();
    }
}
