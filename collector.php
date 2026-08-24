#!/usr/bin/env php
<?php

namespace olvlvl\ComposerAttributeCollector;

use olvlvl\ComposerAttributeCollector\Logger\StdLogger;

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

$log = new StdLogger($config->isDebug);
$collector = new Collector($config, $log);
$collector->dump();
