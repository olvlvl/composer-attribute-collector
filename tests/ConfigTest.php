<?php

namespace tests\olvlvl\ComposerAttributeCollector;

use Composer\Package\RootPackageInterface;
use Composer\PartialComposer;
use Composer\Util\Platform;
use olvlvl\ComposerAttributeCollector\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function preg_match;

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
                Config::EXTRA_STRATEGY => Config::STRATEGY_EMBEDDED,
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
            strategy: Config::STRATEGY_EMBEDDED,
        );

        $actual = Config::from($composer);

        $this->assertEquals($expected, $actual);
    }

    public function testFromFailsOnInvalidStrategy(): void
    {
        $extra = [
            Config::EXTRA => [
                Config::EXTRA_STRATEGY => 'nonsense',
            ],
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

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid strategy 'nonsense'");

        Config::from($composer);
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

    #[DataProvider('provideExclude')]
    public function testExclude(array $exclude, string $subject, bool $expected): void
    {
        $this->assertSame($expected, preg_match(self::excludeRegExp($exclude), $subject) === 1);
    }

    public function testNoExclude(): void
    {
        $config = new Config(
            vendorDir: __DIR__,
            attributesFile: __DIR__ . '/attributes.php',
            include: [ __DIR__ ],
            exclude: [],
            useCache: false,
            isDebug: false,
        );

        $this->assertNull($config->excludeRegExp);
    }

    /**
     * @return array<string, array{ array<non-empty-string>, string, bool }>
     */
    public static function provideExclude(): array
    {
        return [
            'literal directory matches a descendant' => [ [ '/src/Tests' ], '/src/Tests/Foo.php', true ],
            'literal directory matches a nested descendant' => [ [ '/src/Tests' ], '/src/Tests/deep/Foo.php', true ],
            'literal directory does not match a sibling file' => [ [ '/src/Tests' ], '/src/Tests.php', false ],
            'literal directory does not match a prefix' => [ [ '/src/Tests' ], '/src/TestsFoo/Foo.php', false ],
            'trailing separator directory is equivalent' => [ [ '/src/Tests/' ], '/src/Tests/Foo.php', true ],
            'star matches within a path segment' => [ [ '/src/*.php' ], '/src/Foo.php', true ],
            'star does not match across a slash' => [ [ '/src/*.php' ], '/src/deep/Foo.php', false ],
            'double star matches a direct descendant' => [ [ '/src/**' ], '/src/Foo.php', true ],
            'double star matches a nested descendant' => [ [ '/src/**' ], '/src/deep/Foo.php', true ],
            'question mark is a literal' => [ [ '/src/?.php' ], '/src/Foo.php', false ],
            'question mark matches a literal question mark' => [ [ '/src/?.php' ], '/src/?.php', true ],
            'backslashes are normalized' => [ [ '\\src\\Tests' ], '/src/Tests/Foo.php', true ],
            'patterns are OR-ed' => [ [ '/src/Tests', '/lib' ], '/lib/Foo.php', true ],
            'unrelated path is kept' => [ [ '/src/Tests' ], '/lib/Foo.php', false ],
        ];
    }

    /**
     * @param non-empty-string[] $exclude
     *
     * @return non-empty-string
     */
    private static function excludeRegExp(array $exclude): string
    {
        $config = new Config(
            vendorDir: __DIR__,
            attributesFile: __DIR__ . '/attributes.php',
            include: [ __DIR__ ],
            exclude: $exclude,
            useCache: false,
            isDebug: false,
        );

        $regexp = $config->excludeRegExp;

        self::assertNotNull($regexp);

        return $regexp;
    }
}
