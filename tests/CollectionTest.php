<?php

namespace tests\olvlvl\ComposerAttributeCollector;

use Acme\Attribute\ActiveRecord\Column;
use Acme\Attribute\ActiveRecord\Index;
use Acme\Attribute\ActiveRecord\Serial;
use Acme\Attribute\ActiveRecord\Text;
use Acme\Attribute\ActiveRecord\Varchar;
use Acme\Attribute\Get;
use Acme\Attribute\Permission;
use Acme\Attribute\Post;
use Acme\Attribute\Route;
use Acme\Presentation\FileController;
use Acme\Presentation\ImageController;
use Acme\PSR4\ActiveRecord\Article;
use Acme\PSR4\DeleteMenu;
use Acme\PSR4\Presentation\ArticleController;
use Closure;
use olvlvl\ComposerAttributeCollector\Collection;
use olvlvl\ComposerAttributeCollector\TargetClass;
use olvlvl\ComposerAttributeCollector\TargetMethod;
use olvlvl\ComposerAttributeCollector\TargetProperty;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function in_array;

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
            targetClasses: [
                Permission::class => [
                    [ [ 'Permission' => 'is_admin' ], DeleteMenu::class ],
                ]
            ],
            targetMethods: [
                Route::class => [
                    [ [ 'Method' => 'GET' ], ArticleController::class, 'list' ],
                ]
            ],
            targetProperties: [
                Serial::class => [
                    [ [ 'Primary' => true ], Article::class, 'id' ],
                ]
            ]
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
                fn(Collection $c) => $c->findTargetClasses(Permission::class),
            ],
            [
                "An error occurred while instantiating attribute Acme\Attribute\Route on method Acme\PSR4\Presentation\ArticleController::list",
                fn(Collection $c) => $c->findTargetMethods(Route::class),
            ],
            [
                "An error occurred while instantiating attribute Acme\Attribute\ActiveRecord\Serial on property Acme\PSR4\ActiveRecord\Article::id",
                fn(Collection $c) => $c->findTargetProperties(Serial::class),
            ],
            [
                "An error occurred while instantiating attribute Acme\Attribute\Permission on class Acme\PSR4\DeleteMenu",
                fn(Collection $c) => $c->forClass(DeleteMenu::class),
            ],
            [
                "An error occurred while instantiating attribute Acme\Attribute\Route on method Acme\PSR4\Presentation\ArticleController::list",
                fn(Collection $c) => $c->forClass(ArticleController::class),
            ],

        ];
    }

    public function testFilterTargetClasses(): void
    {
        $collection = new Collection(
            targetClasses: [
                Route::class => [
                    [ [ 'pattern' => '/articles' ], ArticleController::class ],
                    [ [ 'pattern' => '/images' ], ImageController::class ],
                    [ [ 'pattern' => '/files' ], FileController::class ],
                ],
            ],
            targetMethods: [
            ],
            targetProperties: [
            ]
        );

        $actual = $collection->filterTargetClasses(
            fn($a, $c) => in_array($c, [ ArticleController::class, ImageController::class ])
        );

        $this->assertEquals([
            new TargetClass(new Route('/articles'), ArticleController::class),
            new TargetClass(new Route('/images'), ImageController::class),
        ], $actual);
    }

    public function testFilterTargetMethods(): void
    {
        $collection = new Collection(
            targetClasses: [
            ],
            targetMethods: [
                Route::class => [
                    [ [ 'pattern' => '/recent' ], ArticleController::class, 'recent' ],
                ],
                Get::class => [
                    [ [ ], ArticleController::class, 'show' ],
                ],
                Post::class => [
                    [ [ ], ArticleController::class, 'create' ],
                ],
            ],
            targetProperties: [
            ]
        );

        $actual = $collection->filterTargetMethods(fn($a) => is_a($a, Route::class, true));

        $this->assertEquals([
            new TargetMethod(new Route('/recent'), ArticleController::class, 'recent'),
            new TargetMethod(new Get(), ArticleController::class, 'show'),
            new TargetMethod(new Post(), ArticleController::class, 'create'),
        ], $actual);
    }

    public function testFilterTargetProperties(): void
    {
        $collection = new Collection(
            targetClasses: [
            ],
            targetMethods: [
                Route::class => [
                    [ [ 'pattern' => '/recent' ], ArticleController::class, 'recent' ],
                ],
                Get::class => [
                    [ [ ], ArticleController::class, 'show' ],
                ],
                Post::class => [
                    [ [ ], ArticleController::class, 'create' ],
                ],
            ],
            targetProperties: [
                Serial::class => [
                    [ [ 'primary' => true ], Article::class, 'id' ],
                ],
                Varchar::class => [
                    [ [ 'size' => 80 ], Article::class, 'title' ],
                ],
                Text::class => [
                    [ [ ], Article::class, 'body' ],
                ]
            ]
        );

        $actual = $collection->filterTargetProperties(fn($a) => is_a($a, Column::class, true));

        $this->assertEquals([
            new TargetProperty(new Serial(primary: true), Article::class, 'id'),
            new TargetProperty(new Varchar(size: 80), Article::class, 'title'),
            new TargetProperty(new Text(), Article::class, 'body'),
        ], $actual);
    }

    public function testForClass(): void
    {
        $collection = new Collection(
            targetClasses: [
                Index::class => [
                    [ [ 'slug', 'unique' => true ], Article::class ],
                ],
                Route::class => [ // trap
                    [ [ 'pattern' => '/articles' ], ArticleController::class ],
                ],
            ],
            targetMethods: [
                Route::class => [ // trap
                    [ [ 'pattern' => '/recent' ], ArticleController::class, 'recent' ],
                ],
            ],
            targetProperties: [
                Serial::class => [
                    [ [ 'primary' => true ], Article::class, 'id' ],
                ],
                Varchar::class => [
                    [ [ 'size' => 80 ], Article::class, 'title' ],
                    [ [ 'size' => 80 ], Article::class, 'slug' ],
                ],
                Text::class => [
                    [ [ ], Article::class, 'body' ],
                ]
            ]
        );

        $actual = $collection->forClass(Article::class);

        $this->assertEquals([
            new Index('slug', unique: true),
        ], $actual->classAttributes);

        $this->assertEmpty($actual->methodsAttributes);

        $this->assertEquals([
            'id' => [
                new Serial(primary: true),
            ],
            'title' => [
                new Varchar(size: 80),
            ],
            'slug' => [
                new Varchar(size: 80),
            ],
            'body' => [
                new Text(),
            ]
        ], $actual->propertyAttributes);
    }
}
