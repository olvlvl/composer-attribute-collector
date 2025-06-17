<?php

namespace tests\olvlvl\ComposerAttributeCollector;

use olvlvl\ComposerAttributeCollector\Collector;
use olvlvl\ComposerAttributeCollector\Config;

final class CollectorTest extends CollectorTestAbstract
{
    protected static function dump(Config $config): void
    {
        $collector = new Collector($config, new FakeLogger());
        $collector->dump();
    }
}
