<?php

namespace olvlvl\ComposerAttributeCollector\Factory;

use olvlvl\ComposerAttributeCollector\CollectionRenderer;
use olvlvl\ComposerAttributeCollector\CollectionRenderer\ReferenceCollectionRenderer;
use olvlvl\ComposerAttributeCollector\CollectionRenderer\StaticCollectionRenderer;
use olvlvl\ComposerAttributeCollector\Config;

/**
 * @internal
 */
final class CollectionRendererFactory
{
    /**
     * Returns the collection renderer to use according to the configuration.
     *
     * @return class-string<CollectionRenderer>
     */
    public static function from(Config $config): string
    {
        return $config->strategy === Config::STRATEGY_STATIC
            ? StaticCollectionRenderer::class
            : ReferenceCollectionRenderer::class;
    }
}
