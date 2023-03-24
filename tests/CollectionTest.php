<?php

namespace tests\olvlvl\ComposerAttributeCollector;

use Closure;
use olvlvl\ComposerAttributeCollector\Collection;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class CollectionTest extends TestCase
{
    /**
     * @dataProvider provideInstantiationErrorIsDecorated
     *
     * @param Closure(Collection):void $act
     */
    public function testInstantiationErrorIsDecorated(string $expectedMessage, Closure $act): void
    {
        $collection = new Collection(
            [
                \Acme\Attribute\Permission::class => [
                    [ [ 'Permission' => 'is_admin' ], \Acme\PSR4\DeleteMenu::class ],
                ]
            ],
            [
                \Acme\Attribute\Route::class => [
                    [ [ 'Method' => 'GET' ], \Acme\PSR4\Presentation\ArticleController::class, 'list' ],
                ]
            ],
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($expectedMessage);
        $act($collection);
    }

    /**
     * @return array<array{ string, Closure }>
     */
    public static function provideInstantiationErrorIsDecorated(): array
    {
        return [

            [
                "An error occurred while instantiating attribute Acme\Attribute\Permission on class Acme\PSR4\DeleteMenu",
                fn(Collection $c) => $c->findTargetClasses(\Acme\Attribute\Permission::class),
            ],
            [
                "An error occurred while instantiating attribute Acme\Attribute\Route on method Acme\PSR4\Presentation\ArticleController::list",
                fn(Collection $c) => $c->findTargetMethods(\Acme\Attribute\Route::class),
            ],
            [
                "An error occurred while instantiating attribute Acme\Attribute\Permission on class Acme\PSR4\DeleteMenu",
                fn(Collection $c) => $c->forClass(\Acme\PSR4\DeleteMenu::class),
            ],
            [
                "An error occurred while instantiating attribute Acme\Attribute\Route on method Acme\PSR4\Presentation\ArticleController::list",
                fn(Collection $c) => $c->forClass(\Acme\PSR4\Presentation\ArticleController::class),
            ],

        ];
    }
}
