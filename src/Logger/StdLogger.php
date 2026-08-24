<?php

namespace olvlvl\ComposerAttributeCollector\Logger;

use olvlvl\ComposerAttributeCollector\Logger;

/**
 * @internal
 */
final readonly class StdLogger implements Logger
{
    public function __construct(
        private bool $isDebug,
    ) {
    }

    public function debug(\Stringable|string $message): void
    {
        if (!$this->isDebug) {
            return;
        }

        fwrite(STDERR, $message . PHP_EOL);
    }

    public function warning(\Stringable|string $message): void
    {
        fwrite(STDERR, "\033[33m$message\033[0m" . PHP_EOL);
    }

    public function error(\Stringable|string $message): void
    {
        fwrite(STDERR, "\033[31m$message\033[0m" . PHP_EOL);
    }
}
