<?php

namespace tests\olvlvl\ComposerAttributeCollector;

use Composer\Package\RootPackageInterface;
use Composer\PartialComposer;
use Composer\Util\Platform;
use olvlvl\ComposerAttributeCollector\Config;
use PHPUnit\Framework\TestCase;
use RuntimeException;

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

        $cwd = Platform::getCwd();
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

    public function testResolveIncludeFromAutoload(): void
    {
        $package = $this->createMock(RootPackageInterface::class);
        $package
            ->method('getExtra')
            ->willReturn([]);
        $package
            ->expects($this->once())
            ->method('getAutoload')
            ->willReturn([
                'classmap' => [
                    'src/classmap',
                    'src/bootstrap.php',
                ],
                'psr-0' => [
                    'Acme/PSR4' => './src/psr-0',
                ],
                'psr-4' => [
                    'Acme/PSR4' => 'src/psr-4',
                ],
                'files' => [
                    './src/files'
                ]
            ]);

        $cwd = Platform::getCwd();
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
                "$cwd/src/classmap",
                "$cwd/src/bootstrap.php",
                "$cwd/src/psr-0",
                "$cwd/src/psr-4",
                "$cwd/src/files",
            ],
            exclude: [],
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
