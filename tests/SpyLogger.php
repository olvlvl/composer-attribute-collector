<?php

namespace tests\olvlvl\ComposerAttributeCollector;

use olvlvl\ComposerAttributeCollector\Logger;

class SpyLogger implements Logger
{
    public array $debugRecords = [];
    public array $warningRecords = [];
    public array $errorRecords = [];

    public function debug(\Stringable|string $message): void
    {
        $this->debugRecords[] = $message;
    }

    public function warning(\Stringable|string $message): void
    {
        $this->warningRecords[] = $message;
    }

    public function error(\Stringable|string $message): void
    {
        $this->errorRecords[] = $message;
    }
}
