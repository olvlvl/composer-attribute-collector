<?php

namespace olvlvl\ComposerAttributeCollector;

/**
 * @internal
 */
interface Logger
{
    public function debug(string|\Stringable $message): void;
    public function warning(string|\Stringable $message): void;
    public function error(string|\Stringable $message): void;
}
