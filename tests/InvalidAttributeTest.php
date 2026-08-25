<?php

namespace tests\olvlvl\ComposerAttributeCollector;

use olvlvl\ComposerAttributeCollector\Collector;
use olvlvl\ComposerAttributeCollector\Config;
use PHPUnit\Framework\TestCase;

final class InvalidAttributeTest extends TestCase
{
    public function testInvalidAttribute(): void
    {
        $config = self::makeConfig();
        $logger = new SpyLogger();
        $collector = new Collector($config, $logger);

        $collector->run();

        $expected = [
            "Discarding 'Acme\PSR4\MissingInterface' because an error occurred during loading: Interface \"Acme\PSR4\UndefinedInterface\" not found",
            "Discarding 'Acme\PSR4\MissingParent' because an error occurred during loading: Class \"Acme\PSR4\UndefinedParent\" not found",
        ];

        $this->assertEquals($expected, $logger->warningRecords);
    }

    private static function makeConfig(): Config
    {
        $cwd = getcwd();
        assert(is_string($cwd));
        $vendorDir = __DIR__ . '/sandbox';
        $filepath = "$vendorDir/attributes.php";
        $exclude = [
            "$cwd/tests/Acme/PSR4/IncompatibleSignature.php",
            "$cwd/tests/Acme85",
        ];

        return new Config(
            vendorDir: $vendorDir,
            attributesFile: $filepath,
            include: [
                "$cwd/tests",
            ],
            exclude: $exclude,
            useCache: false,
            isDebug: false,
        );
    }
}
