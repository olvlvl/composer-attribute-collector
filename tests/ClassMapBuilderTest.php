<?php

/*
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\olvlvl\ComposerAttributeCollector;

use olvlvl\ComposerAttributeCollector\ClassMapBuilder;
use PHPUnit\Framework\TestCase;

use function getcwd;

final class ClassMapBuilderTest extends TestCase
{
    public function testBuildClassMap(): void
    {
        $sut = new ClassMapBuilder();
        $classMap = $sut->buildClassMap([
            'psr-0' => [
            ],
            'psr-4' => [
                "Acme\\PSR4\\" => [
                    __DIR__ . '/Acme/PSR4'
                ],
            ],
            'classmap' => [
                __DIR__ . '/Acme/ClassMap'
            ],
            'exclude-from-classmap' => [
            ]
        ]);

        $cwd = getcwd();

        $expected = [
            'Acme\PSR4\CreateMenu' => "$cwd/tests/Acme/PSR4/CreateMenu.php",
            'Acme\PSR4\CreateMenuHandler' => "$cwd/tests/Acme/PSR4/CreateMenuHandler.php",
            'Acme\PSR4\DeleteMenu' => "$cwd/tests/Acme/PSR4/DeleteMenu.php",
            'Acme\PSR4\DeleteMenuHandler' => "$cwd/tests/Acme/PSR4/DeleteMenuHandler.php",
            'Acme\PSR4\EventA' => "$cwd/tests/Acme/PSR4/EventA.php",
            'Acme\PSR4\Presentation\ArticleController' => "$cwd/tests/Acme/PSR4/Presentation/ArticleController.php",
            'Acme\PSR4\SubscriberA' => "$cwd/tests/Acme/PSR4/SubscriberA.php",
            'Acme\PSR4\SubscriberB' => "$cwd/tests/Acme/PSR4/SubscriberB.php",
            'Acme\Presentation\ImageController' => "$cwd/tests/Acme/ClassMap/controllers.php",
            'Acme\Presentation\FileController' => "$cwd/tests/Acme/ClassMap/controllers.php",
        ];

        $this->assertEquals($expected, $classMap);
    }
}
