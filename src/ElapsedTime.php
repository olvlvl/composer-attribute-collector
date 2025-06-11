<?php

namespace olvlvl\ComposerAttributeCollector;

/**
 * @internal
 */
final class ElapsedTime
{
    /**
     * @param float $start
     *     Start microtime.
     */
    public static function render(float $start): string
    {
        return sprintf("%.03f ms", (microtime(true) - $start) * 1000);
    }
}
