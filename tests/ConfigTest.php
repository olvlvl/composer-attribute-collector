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
                Config::EXTRA_IGNORE_PATHS => [
                    '{vendor}/vendor1/package1',
                    'tests/Acme/PSR4/IncompatibleSignature.php'
                ]
            ]
        ];

        $package = $this->getMockBuilder(RootPackageInterface::class)->getMock();
        $package
            ->method('getExtra')
            ->willReturn($extra);

        $cwd = getcwd();
        assert(is_string($cwd));
        $config = $this->getMockBuilder(\Composer\Config::class)->getMock();
        $config
            ->method('get')
            ->with('vendor-dir')
            ->willReturn("$cwd/vendor");

        $composer = new PartialComposer();
        $composer->setConfig($config);
        $composer->setPackage($package);

        $expected = new Config(
            attributesFile: "$cwd/vendor/attributes.php",
            ignorePaths: [
                "$cwd/vendor/vendor1/package1",
                "$cwd/tests/Acme/PSR4/IncompatibleSignature.php",
                "$cwd/vendor/symfony/cache/Traits",
            ]
        );

        $actual = Config::from($composer);

        $this->assertEquals($expected, $actual);
    }

    public function testFromFailsOnMissingVendorDir(): void
    {
        $config = $this->getMockBuilder(\Composer\Config::class)->getMock();
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
