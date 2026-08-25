<?php

namespace tests\olvlvl\ComposerAttributeCollector\Factory;

use olvlvl\ComposerAttributeCollector\CollectionRenderer\EmbeddedCollectionRenderer;
use olvlvl\ComposerAttributeCollector\CollectionRenderer\ReferenceCollectionRenderer;
use olvlvl\ComposerAttributeCollector\Config;
use olvlvl\ComposerAttributeCollector\Factory\CollectionRendererFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CollectionRendererFactoryTest extends TestCase
{
    #[DataProvider('provideRenderer')]
    public function testFrom(string $strategy, string $expected): void
    {
        $config = self::makeConfig($strategy);

        $this->assertSame($expected, CollectionRendererFactory::from($config));
    }

    /**
     * @return array<string, array{ string, class-string }>
     */
    public static function provideRenderer(): array
    {
        return [
            'embedded' => [ Config::STRATEGY_EMBEDDED, EmbeddedCollectionRenderer::class ],
            'reference' => [ Config::STRATEGY_REFERENCE, ReferenceCollectionRenderer::class ],
        ];
    }

    private static function makeConfig(string $strategy): Config
    {
        return new Config(
            vendorDir: __DIR__,
            attributesFile: __DIR__ . '/attributes.php',
            include: [ __DIR__ ],
            exclude: [],
            useCache: false,
            isDebug: false,
            strategy: $strategy,
        );
    }
}
