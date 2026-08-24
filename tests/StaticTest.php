<?php

namespace tests\olvlvl\ComposerAttributeCollector;

use olvlvl\ComposerAttributeCollector\Collector;
use olvlvl\ComposerAttributeCollector\Config;

final class StaticTest extends TestAbstract
{
    #[\Override]
    protected static function getStrategy(): string
    {
        return Config::STRATEGY_STATIC;
    }

    protected static function dump(Config $config): void
    {
        $collector = new Collector($config, new FakeLogger());
        $collector->dump();
    }
}
