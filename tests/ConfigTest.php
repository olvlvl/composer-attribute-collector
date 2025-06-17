<?php

namespace tests\olvlvl\ComposerAttributeCollector;

use Composer\Package\RootPackageInterface;
use Composer\PartialComposer;
use olvlvl\ComposerAttributeCollector\Config;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function assert;
use function getcwd;
use function is_string;

final class ConfigTest extends TestCase
{
    public function testFrom(): void
    {
        $extra = [
            Config::EXTRA => [
                Config::EXTRA_INCLUDE => [
                    'tests',
                    '{vendor}/vendor1/package1',
                ],
                Config::EXTRA_EXCLUDE => [
                    'tests/Acme/PSR4/IncompatibleSignature.php',
                    '{vendor}/vendor1/package1/file.php',
                ],
            ]
        ];

        $package = $this->createMock(RootPackageInterface::class);
        $package
            ->method('getExtra')
            ->willReturn($extra);

        $cwd = getcwd();
        assert(is_string($cwd));
        $config = $this->createMock(\Composer\Config::class);
        $config
            ->method('get')
            ->with('vendor-dir')
            ->willReturn("$cwd/vendor");

        $composer = new PartialComposer();
        $composer->setConfig($config);
        $composer->setPackage($package);

        $expected = new Config(
            vendorDir: "$cwd/vendor",
            attributesFile: "$cwd/vendor/attributes.php",
            include: [
                "$cwd/tests",
                "$cwd/vendor/vendor1/package1",
            ],
            exclude: [
                "$cwd/tests/Acme/PSR4/IncompatibleSignature.php",
                "$cwd/vendor/vendor1/package1/file.php",
            ],
            useCache: false,
            isDebug: false,
        );

        $actual = Config::from($composer);

        $this->assertEquals($expected, $actual);
    }

    public function testFromFailsOnMissingVendorDir(): void
    {
        $config = $this->createMock(\Composer\Config::class);
        $config
            ->method('get')
            ->with('vendor-dir')
            ->willReturn("");

        $composer = new PartialComposer();
        $composer->setConfig($config);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Unable to determine vendor directory");

        Config::from($composer);
    }
}
