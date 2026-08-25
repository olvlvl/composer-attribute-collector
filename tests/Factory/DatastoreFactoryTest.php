<?php

namespace tests\olvlvl\ComposerAttributeCollector\Factory;

use olvlvl\ComposerAttributeCollector\Config;
use olvlvl\ComposerAttributeCollector\Datastore\FileDatastore;
use olvlvl\ComposerAttributeCollector\Datastore\RuntimeDatastore;
use olvlvl\ComposerAttributeCollector\Factory\DatastoreFactory;
use PHPUnit\Framework\TestCase;
use tests\olvlvl\ComposerAttributeCollector\FakeLogger;

final class DatastoreFactoryTest extends TestCase
{
    public function testFromWithoutCache(): void
    {
        $config = self::makeConfig(useCache: false);

        $datastore = DatastoreFactory::from($config, new FakeLogger());

        $this->assertInstanceOf(RuntimeDatastore::class, $datastore);
    }

    public function testFromWithCache(): void
    {
        $config = self::makeConfig(useCache: true);

        $datastore = DatastoreFactory::from($config, new FakeLogger());

        $this->assertInstanceOf(FileDatastore::class, $datastore);
    }

    private static function makeConfig(bool $useCache): Config
    {
        return new Config(
            vendorDir: __DIR__,
            attributesFile: __DIR__ . '/attributes.php',
            include: [ __DIR__ ],
            exclude: [],
            useCache: $useCache,
            isDebug: false,
            strategy: Config::STRATEGY_STATIC,
        );
    }
}
