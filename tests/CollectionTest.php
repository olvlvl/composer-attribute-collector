<?php

namespace tests\olvlvl\ComposerAttributeCollector;

use Acme\Attribute\ActiveRecord\Id;
use Acme\Attribute\ActiveRecord\Index;
use Acme\Attribute\ActiveRecord\SchemaAttribute;
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
use Acme81\Attribute\ParameterA;
use Acme81\Attribute\ParameterB;
use olvlvl\ComposerAttributeCollector\Attributes;
use olvlvl\ComposerAttributeCollector\Collection;
use olvlvl\ComposerAttributeCollector\TargetClass;
use olvlvl\ComposerAttributeCollector\TargetMethod;
use olvlvl\ComposerAttributeCollector\TargetParameter;
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
                    [ serialize([ 'Permission' => 'is_admin' ]), DeleteMenu::class ],
                ]
            ],
            targetMethods: [
                Route::class => [
                    [ serialize([ 'Method' => 'GET' ]), ArticleController::class, 'list' ],
                ]
            ],
            targetProperties: [
                Serial::class => [
                    [ serialize([ 'Primary' => true ]), Article::class, 'id' ],
                ]
            ],
            targetParameters: [
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
                    [ serialize([ 'pattern' => '/articles' ]), ArticleController::class ],
                    [ serialize([ 'pattern' => '/images' ]), ImageController::class ],
                    [ serialize([ 'pattern' => '/files' ]), FileController::class ],
                ],
            ],
            targetMethods: [
            ],
            targetProperties: [
            ],
            targetParameters: [
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
                    [ serialize([ 'pattern' => '/recent' ]), ArticleController::class, 'recent' ],
                ],
                Get::class => [
                    [ serialize([ ]), ArticleController::class, 'show' ],
                ],
                Post::class => [
                    [ serialize([ ]), ArticleController::class, 'create' ],
                ],
            ],
            targetProperties: [
            ],
            targetParameters: [
            ]
        );

        $actual = $collection->filterTargetMethods(fn($a) => is_a($a, Route::class, true));

        $this->assertEquals([
            new TargetMethod(new Route('/recent'), ArticleController::class, 'recent'),
            new TargetMethod(new Get(), ArticleController::class, 'show'),
            new TargetMethod(new Post(), ArticleController::class, 'create'),
        ], $actual);
    }

    public function testFilterTargetParameters(): void
    {
        $collection = new Collection(
            targetClasses: [
            ],
            targetMethods: [
            ],
            targetProperties: [
            ],
            targetParameters: [
                ParameterA::class => [
                    [ serialize([ 'a' ]), ArticleController::class, 'myMethod', 'myParamA', ],
                    [ serialize([ 'a2' ]), ArticleController::class, 'myMethod', 'myParamA2' ],
                    [ serialize([ 'a3' ]), ArticleController::class, 'myFoo', 'fooParam' ],
                ],
                ParameterB::class => [
                    [ serialize([ 'b', 'more data']), ArticleController::class, 'myMethod', 'myParamB' ],
                ],
            ]
        );

        $actual = $collection->filterTargetParameters(fn($a) => is_a($a, ParameterA::class, true));

        $this->assertEquals([
            new TargetParameter(new ParameterA('a'), ArticleController::class, 'myMethod', 'myParamA'),
            new TargetParameter(new ParameterA('a2'), ArticleController::class, 'myMethod', 'myParamA2'),
            new TargetParameter(new ParameterA('a3'), ArticleController::class, 'myFoo', 'fooParam'),
        ], $actual);
    }

    public function testFilterTargetProperties(): void
    {
        $collection = new Collection(
            targetClasses: [
            ],
            targetMethods: [
                Route::class => [
                    [ serialize([ 'pattern' => '/recent' ]), ArticleController::class, 'recent' ],
                ],
                Get::class => [
                    [ serialize([ ]), ArticleController::class, 'show' ],
                ],
                Post::class => [
                    [ serialize([ ]), ArticleController::class, 'create' ],
                ],
            ],
            targetProperties: [
                Id::class => [
                    [ serialize([ ]), Article::class, 'id' ],
                ],
                Serial::class => [
                    [ serialize([ ]), Article::class, 'id' ],
                ],
                Varchar::class => [
                    [ serialize([ 'size' => 80 ]), Article::class, 'title' ],
                ],
                Text::class => [
                    [ serialize([ ]), Article::class, 'body' ],
                ]
            ],
            targetParameters: [
            ]
        );

        $actual = $collection->filterTargetProperties(
            Attributes::predicateForAttributeInstanceOf(SchemaAttribute::class)
        );

        $this->assertEquals([
            new TargetProperty(new Id(), Article::class, 'id'),
            new TargetProperty(new Serial(), Article::class, 'id'),
            new TargetProperty(new Varchar(size: 80), Article::class, 'title'),
            new TargetProperty(new Text(), Article::class, 'body'),
        ], $actual);
    }

    public function testForClass(): void
    {
        $collection = new Collection(
            targetClasses: [
                Index::class => [
                    [ serialize([ 'slug', 'unique' => true ]), Article::class ],
                ],
                Route::class => [ // trap
                    [ serialize([ 'pattern' => '/articles' ]), ArticleController::class ],
                ],
            ],
            targetMethods: [
                Route::class => [ // trap
                    [ serialize([ 'pattern' => '/recent' ]), ArticleController::class, 'recent' ],
                ],
            ],
            targetProperties: [
                Id::class => [
                    [ serialize([ ]), Article::class, 'id' ],
                ],
                Serial::class => [
                    [ serialize([ ]), Article::class, 'id' ],
                ],
                Varchar::class => [
                    [ serialize([ 'size' => 80 ]), Article::class, 'title' ],
                    [ serialize([ 'size' => 80 ]), Article::class, 'slug' ],
                ],
                Text::class => [
                    [ serialize([ ]), Article::class, 'body' ],
                ]
            ],
            targetParameters: [
            ]
        );

        $actual = $collection->forClass(Article::class);

        $this->assertEquals([
            new Index('slug', unique: true),
        ], $actual->classAttributes);

        $this->assertEmpty($actual->methodsAttributes);

        $this->assertEquals([
            'id' => [
                new Id(),
                new Serial(),
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
