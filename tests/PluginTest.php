<?php

/*
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\olvlvl\ComposerAttributeCollector;

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

use function usort;

final class PluginTest extends TestCase
{
    public function testDump(): void
    {
        $extra = [
            Plugin::EXTRA => [
                Plugin::EXTRA_IGNORE_PATHS => [
                    'IncompatibleSignature'
                ]
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

        $targets = Attributes::findTargetClasses(Permission::class);

        $this->assertEquals([
            [ new Permission('is_admin'), \Acme\PSR4\CreateMenu::class ],
            [ new Permission('can_create_menu'), \Acme\PSR4\CreateMenu::class ],
            [ new Permission('is_admin'), \Acme\PSR4\DeleteMenu::class ],
            [ new Permission('can_delete_menu'), \Acme\PSR4\DeleteMenu::class ],
        ], $this->collectClasses($targets));

        $targets = Attributes::findTargetClasses(Handler::class);

        $this->assertEquals([
            [ new Handler(), \Acme\PSR4\CreateMenuHandler::class ],
            [ new Handler(), \Acme\PSR4\DeleteMenuHandler::class ],
        ], $this->collectClasses($targets));

        $targets = Attributes::findTargetMethods(Route::class);

        $this->assertEquals([
            [ new Route("/articles"), 'Acme\PSR4\Presentation\ArticleController::list' ],
            [ new Route("/articles/{id}"), 'Acme\PSR4\Presentation\ArticleController::show' ],
            [ new Route("/files"), 'Acme\Presentation\FileController::list' ],
            [ new Route("/files/{id}"), 'Acme\Presentation\FileController::show' ],
            [ new Route("/images"), 'Acme\Presentation\ImageController::list' ],
            [ new Route("/images/{id}"), 'Acme\Presentation\ImageController::show' ],
        ], $this->collectMethods($targets));

        $targets = Attributes::findTargetMethods(Subscribe::class);

        $this->assertEquals([
            [ new Subscribe(), 'Acme\PSR4\SubscriberA::onEventA' ],
            [ new Subscribe(), 'Acme\PSR4\SubscriberB::onEventA' ],
        ], $this->collectMethods($targets));

        $forClass = Attributes::forClass(ArticleController::class);

        $this->assertEquals([
            new Resource('articles'),
        ], $forClass->classAttributes);

        $this->assertEquals([
            'list' => [ new Route("/articles") ],
            'show' => [ new Route("/articles/{id}") ],
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
