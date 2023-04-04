<?php

/*
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\olvlvl\ComposerAttributeCollector;

use Acme\Attribute\Get;
use Acme\Attribute\Handler;
use Acme\Attribute\Permission;
use Acme\Attribute\Resource;
use Acme\Attribute\Route;
use Acme\Attribute\Subscribe;
use Acme\PSR4\Presentation\ArticleController;
use Composer\Composer;
use Composer\IO\NullIO;
use Composer\Package\RootPackageInterface;
use olvlvl\ComposerAttributeCollector\Attributes;
use olvlvl\ComposerAttributeCollector\AutoloadsBuilder;
use olvlvl\ComposerAttributeCollector\Plugin;
use olvlvl\ComposerAttributeCollector\TargetClass;
use olvlvl\ComposerAttributeCollector\TargetMethod;
use PHPUnit\Framework\TestCase;

use function str_contains;
use function usort;

final class PluginTest extends TestCase
{
    private static bool $initialized = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (self::$initialized) {
            return;
        }

        $extra = [
            'composer-attribute-collector' => [
                'ignore-paths' => [
                    'tests/Acme/PSR4/IncompatibleSignature',
                    'CreateMenu', // should not match anything because it is not relative to project root
                ],
                'include-paths' => [
                    'tests/Acme/',
                ],
            ]
        ];

        $package = $this->getMockBuilder(RootPackageInterface::class)->getMock();
        $package
            ->method('getExtra')
            ->willReturn($extra);

        $composer = $this->getMockBuilder(Composer::class)->getMock();
        $composer
            ->method('getPackage')
            ->willReturn($package);

        $autoloadsBuilder = $this->getMockBuilder(AutoloadsBuilder::class)->getMock();
        $autoloadsBuilder
            ->method('buildAutoloads')
            ->with($composer)
            ->willReturn(
                [
                    'psr-0' => [],
                    'psr-4' => [
                        "Acme\\PSR4\\" => [
                            __DIR__ . '/Acme/PSR4'
                        ],
                    ],
                    'classmap' => [
                        __DIR__ . '/Acme/ClassMap'
                    ],
                    'exclude-from-classmap' => []
                ]
            );

        $filepath = __DIR__ . '/sandbox/attributes.php';

        Plugin::dump(
            $composer,
            new NullIO(),
            $filepath,
            $autoloadsBuilder
        );

        $this->assertFileExists($filepath);

        require $filepath;

        self::$initialized = true;
    }

    /**
     * @dataProvider provideTargetClasses
     *
     * @param class-string $attribute
     * @param array<array{ object, class-string }> $expected
     */
    public function testTargetClasses(string $attribute, array $expected): void
    {
        $actual = Attributes::findTargetClasses($attribute);

        $this->assertEquals($expected, $this->collectClasses($actual));
    }

    /**
     * @return array<array{ class-string, array<array{ object, class-string }> }>
     */
    public static function provideTargetClasses(): array
    {
        return [

            [
                Permission::class,
                [
                    [ new Permission('is_admin'), \Acme\PSR4\CreateMenu::class ],
                    [ new Permission('can_create_menu'), \Acme\PSR4\CreateMenu::class ],
                    [ new Permission('is_admin'), \Acme\PSR4\DeleteMenu::class ],
                    [ new Permission('can_delete_menu'), \Acme\PSR4\DeleteMenu::class ],
                ]
            ],
            [
                Handler::class,
                [
                    [ new Handler(), \Acme\PSR4\CreateMenuHandler::class ],
                    [ new Handler(), \Acme\PSR4\DeleteMenuHandler::class ],
                ]
            ],

        ];
    }

    /**
     * @dataProvider provideTargetMethods
     *
     * @param class-string $attribute
     * @param array<array{ object, callable-string }> $expected
     */
    public function testTargetMethods(string $attribute, array $expected): void
    {
        $actual = Attributes::findTargetMethods($attribute);

        $this->assertEquals($expected, $this->collectMethods($actual));
    }

    /**
     * @return array<array{ class-string, array<array{ object, callable-string }> }>
     */
    public static function provideTargetMethods(): array
    {
        return [

            [
                Route::class,
                [
                    [ new Route("/articles", 'GET', 'articles:list'), 'Acme\PSR4\Presentation\ArticleController::list' ],
                    [ new Route("/articles/{id}", 'GET', 'articles:show'), 'Acme\PSR4\Presentation\ArticleController::show' ],
                ]
            ],
            [
                Get::class,
                [
                    [ new Get(), 'Acme\Presentation\FileController::list' ],
                    [ new Get('/{id}'), 'Acme\Presentation\FileController::show' ],
                    [ new Get(), 'Acme\Presentation\ImageController::list' ],
                    [ new Get('/{id}'), 'Acme\Presentation\ImageController::show' ],
                ]
            ],
            [
                Subscribe::class,
                [
                    [ new Subscribe(), 'Acme\PSR4\SubscriberA::onEventA' ],
                    [ new Subscribe(), 'Acme\PSR4\SubscriberB::onEventA' ],
                ]
            ],

        ];
    }

    public function testFilterTargetClasses(): void
    {
        $actual = Attributes::filterTargetClasses(
            fn($attribute, $class) => str_contains($class, 'Menu')
        );

        $this->assertEquals([
            [ new Permission('is_admin'), \Acme\PSR4\CreateMenu::class ],
            [ new Permission('can_create_menu'), \Acme\PSR4\CreateMenu::class ],
            [ new Handler(), \Acme\PSR4\CreateMenuHandler::class ],
            [ new Permission('is_admin'), \Acme\PSR4\DeleteMenu::class ],
            [ new Permission('can_delete_menu'), \Acme\PSR4\DeleteMenu::class ],
            [ new Handler(), \Acme\PSR4\DeleteMenuHandler::class ],
        ], $this->collectClasses($actual));
    }

    public function testFilterTargetMethods(): void
    {
        $actual = Attributes::filterTargetMethods(
            Attributes::predicateForAttributeInstanceOf(Route::class)
        );

        $this->assertEquals([
            [ new Route("/articles", 'GET', 'articles:list'), 'Acme\PSR4\Presentation\ArticleController::list' ],
            [ new Route("/articles/{id}", 'GET', 'articles:show'), 'Acme\PSR4\Presentation\ArticleController::show' ],
            [ new Get(), 'Acme\Presentation\FileController::list' ],
            [ new Get('/{id}'), 'Acme\Presentation\FileController::show' ],
            [ new Get(), 'Acme\Presentation\ImageController::list' ],
            [ new Get('/{id}'), 'Acme\Presentation\ImageController::show' ],
        ], $this->collectMethods($actual));
    }

    public function testForClass(): void
    {
        $forClass = Attributes::forClass(ArticleController::class);

        $this->assertEquals([
            new Resource('articles'),
        ], $forClass->classAttributes);

        $this->assertEquals([
            'list' => [ new Route("/articles", id: 'articles:list') ],
            'show' => [ new Route("/articles/{id}", id: 'articles:show') ],
        ], $forClass->methodsAttributes);
    }

    /**
     * @template T of object
     *
     * @param TargetClass<T>[] $targets
     *
     * @return array<array{T, class-string}>
     */
    private function collectClasses(array $targets): array
    {
        $methods = [];

        foreach ($targets as $target) {
            $methods[] = [ $target->attribute, $target->name ];
        }

        usort($methods, fn ($a, $b) => $a[1] <=> $b[1]);

        return $methods;
    }

    /**
     * @template T of object
     *
     * @param TargetMethod<T>[] $targets
     *
     * @return array<array{T, string}>
     */
    private function collectMethods(array $targets): array
    {
        $methods = [];

        foreach ($targets as $target) {
            $methods[] = [ $target->attribute, "$target->class::$target->name" ];
        }

        usort($methods, fn ($a, $b) => $a[1] <=> $b[1]);

        return $methods;
    }
}
