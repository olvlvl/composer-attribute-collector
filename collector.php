#!/usr/bin/env php
<?php

namespace olvlvl\ComposerAttributeCollector;

require 'vendor/autoload.php';

$configFile = $argv[1]
    ?? throw new \Exception("Configuration file is missing");

$serializedConfig = file_get_contents($configFile)
    ?: throw new \Exception("Unable to read configuration file");

/** @var Config $config */
$config = unserialize($serializedConfig, [
    'allowed_classes' => [
        Config::class,
    ],
]);

$log = new class($config->isDebug) implements Logger
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
};

$collector = new Collector($config, $log);
$collector->dump();
