<?php

namespace Acme;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

#[SampleAttribute]
class MyLogger implements LoggerInterface
{
    use LoggerTrait;

    public function log($level, \Stringable|string $message, array $context = []): void
    {
    }
}
