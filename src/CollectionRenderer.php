<?php

namespace olvlvl\ComposerAttributeCollector;

/**
 * An interface for an attribute collection renderer.
 */
interface CollectionRenderer
{
    /**
     * Renders a {@link TransientCollection} into PHP code.
     */
    public static function render(TransientCollection $collector): string;
}
