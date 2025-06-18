<?php

/*
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\olvlvl\ComposerAttributeCollector;

use Acme\Attribute\ActiveRecord\Boolean;
use Acme\Attribute\ActiveRecord\Id;
use Acme\Attribute\ActiveRecord\Index;
use Acme\Attribute\ActiveRecord\SchemaAttribute;
use Acme\Attribute\ActiveRecord\Serial;
use Acme\Attribute\ActiveRecord\Text;
use Acme\Attribute\ActiveRecord\Varchar;
use Acme\Attribute\AutowiredService;
use Acme\Attribute\Get;
use Acme\Attribute\Handler;
use Acme\Attribute\Permission;
use Acme\Attribute\Resource;
use Acme\Attribute\Route;
use Acme\Attribute\Routing\UrlGetter;
use Acme\Attribute\Subscribe;
use Acme\PSR4\Presentation\ArticleController;
use Acme81\Attribute\ParameterA;
use Acme81\Attribute\ParameterB;
use olvlvl\ComposerAttributeCollector\Attributes;
use olvlvl\ComposerAttributeCollector\Config;
use olvlvl\ComposerAttributeCollector\TargetClass;
use olvlvl\ComposerAttributeCollector\TargetMethod;
use olvlvl\ComposerAttributeCollector\TargetParameter;
use olvlvl\ComposerAttributeCollector\TargetProperty;
use PhpParser\Node\Param;
use PHPUnit\Framework\TestCase;
use ReflectionException;

use function getcwd;
use function is_string;
use function str_contains;
use function usort;

use const PHP_VERSION_ID;

abstract class CollectorTestAbstract extends TestCase
{
    /**
     * @var array<class-string, bool>
     */
    private static array $initialized = [];

    /**
     * @throws ReflectionException
     */
    final protected function setUp(): void
    {
        parent::setUp();

        if (self::$initialized[get_called_class()] ?? false) {
            return;
        }

        $config = self::makeConfig();

        static::dump($config);

        $filepath = $config->attributesFile;
        $this->assertFileExists($filepath);
        require $filepath;

        self::$initialized[get_called_class()] = true;
    }

    abstract protected static function dump(Config $config): void;

    private static function makeConfig(): Config
    {
        $cwd = getcwd();
        assert(is_string($cwd));
        $vendorDir = __DIR__ . '/sandbox';
        $filepath = "$vendorDir/attributes.php";
        $exclude = [
            "$cwd/tests/Acme/PSR4/IncompatibleSignature.php",
        ];

        if (PHP_VERSION_ID < 80100) {
            $exclude[] = "$cwd/tests/Acme81";
        }

        return new Config(
            vendorDir: $vendorDir,
            attributesFile: $filepath,
            include: [
                "$cwd/tests",
            ],
            exclude: $exclude,
            useCache: false,
            isDebug: false,
        );
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
                ],
            ],
            [
                Handler::class,
                [
                    [ new Handler(), \Acme\PSR4\CreateMenuHandler::class ],
                    [ new Handler(), \Acme\PSR4\DeleteMenuHandler::class ],
                ],
            ],
            [
                Index::class,
                [
                    [ new Index('active'), \Acme\PSR4\ActiveRecord\Article::class ],
                ],
            ],
            [
                AutowiredService::class,
                [
                    [
                        new AutowiredService(factory: '@Acme\PSR4\SignatureMap\SignatureMapProviderFactory::create'),
                        \Acme\PSR4\SignatureMapProvider::class,
                    ],
                ],
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
                    [
                        new Route("/articles/method/", 'GET', 'articles:method'),
                        'Acme\PSR4\Presentation\ArticleController::aMethod',
                    ],
                    [
                        new Route("/articles", 'GET', 'articles:list'),
                        'Acme\PSR4\Presentation\ArticleController::list',
                    ],
                    [
                        new Route("/articles/{id}", 'GET', 'articles:show'),
                        'Acme\PSR4\Presentation\ArticleController::show',
                    ],
                ],
            ],
            [
                Get::class,
                [
                    [ new Get(), 'Acme\Presentation\FileController::list' ],
                    [ new Get('/{id}'), 'Acme\Presentation\FileController::show' ],
                    [ new Get(), 'Acme\Presentation\ImageController::list' ],
                    [ new Get('/{id}'), 'Acme\Presentation\ImageController::show' ],
                ],
            ],
            [
                Subscribe::class,
                [
                    [ new Subscribe(), 'Acme\PSR4\SubscriberA::onEventA' ],
                    [ new Subscribe(), 'Acme\PSR4\SubscriberB::onEventA' ],
                ],
            ],
            [
                UrlGetter::class,
                [
                    [ new UrlGetter(), 'Acme\PSR4\InheritedAttributeSample::get_url' ],
                    [ new UrlGetter(), 'Acme\PSR4\Routing\UrlTrait::get_url' ],
                ],
            ],

        ];
    }

    /**
     * @dataProvider provideTargetParameters
     *
     * @param class-string $attribute
     * @param array<array{ object, callable-string }> $expected
     */
    public function testTargetParameters(string $attribute, array $expected): void
    {
        $actual = Attributes::findTargetParameters($attribute);

        $this->assertEquals($expected, $this->collectParameters($actual));
    }

    /**
     * @return array<array{ class-string, array<array{ object, callable-string }> }>
     */
    public static function provideTargetParameters(): array
    {
        return [

            [
                ParameterA::class,
                [
                    [
                        new ParameterA('my parameter label'),
                        'Acme\PSR4\Presentation\ArticleController::aMethod(myParameter)',
                    ],
                    [
                        new ParameterA('my yet another parameter label'),
                        'Acme\PSR4\Presentation\ArticleController::aMethod(yetAnotherParameter)',
                    ],
                ],
            ],
            [
                ParameterB::class,
                [
                    [
                        new ParameterB('my 2nd parameter label', 'some more data'),
                        'Acme\PSR4\Presentation\ArticleController::aMethod(anotherParameter)',
                    ],
                ],
            ],

        ];
    }

    /**
     * @dataProvider provideTargetProperties
     *
     * @param class-string $attribute
     * @param array<array{ object, string }> $expected
     */
    public function testTargetProperties(string $attribute, array $expected): void
    {
        $actual = Attributes::findTargetProperties($attribute);

        $this->assertEquals($expected, $this->collectProperties($actual));
    }

    /**
     * @return array<array{ class-string, array<array{ object, string }> }>
     */
    public static function provideTargetProperties(): array
    {
        return [

            [
                Serial::class,
                [
                    [ new Serial(), 'Acme\PSR4\ActiveRecord\Article::id' ],
                ],
            ],

            [
                Varchar::class,
                [
                    [ new Varchar(80, unique: true), 'Acme\PSR4\ActiveRecord\Article::slug' ],
                    [ new Varchar(80), 'Acme\PSR4\ActiveRecord\Article::title' ],
                ],
            ],

            [
                Text::class,
                [
                    [ new Text(), 'Acme\PSR4\ActiveRecord\Article::body' ],
                ],
            ],

        ];
    }

    public function testFilterTargetClasses(): void
    {
        $actual = Attributes::filterTargetClasses(
            fn($attribute, $class) => str_contains($class, 'Menu'),
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
            Attributes::predicateForAttributeInstanceOf(Route::class),
        );

        $this->assertEquals([
            [
                new Route("/articles/method/", 'GET', 'articles:method'),
                'Acme\PSR4\Presentation\ArticleController::aMethod',
            ],
            [ new Route("/articles", 'GET', 'articles:list'), 'Acme\PSR4\Presentation\ArticleController::list' ],
            [ new Route("/articles/{id}", 'GET', 'articles:show'), 'Acme\PSR4\Presentation\ArticleController::show' ],
            [ new Get(), 'Acme\Presentation\FileController::list' ],
            [ new Get('/{id}'), 'Acme\Presentation\FileController::show' ],
            [ new Get(), 'Acme\Presentation\ImageController::list' ],
            [ new Get('/{id}'), 'Acme\Presentation\ImageController::show' ],
        ], $this->collectMethods($actual));
    }

    /**
     * @requires PHP >= 8.1
     */
    public function testNestedAttributes81(): void
    {
        $expected = [
            new TargetClass(
                new \Acme81\Attribute\SampleNested(new \Acme81\Attribute\SampleNestedValue(1)),
                \Acme81\PSR4\Presentation\ArticleController::class,
            )
        ];
        $actual = Attributes::filterTargetClasses(
            Attributes::predicateForAttributeInstanceOf(\Acme81\Attribute\SampleNested::class)
        );

        $this->assertEquals($expected, $actual);
    }

    /**
     * @requires PHP >= 8.1
     */
    public function testFilterTargetMethods81(): void
    {
        $expected = [
            new TargetMethod(
                new \Acme81\Attribute\Route('/:id', method: \Acme81\Attribute\Method::GET),
                \Acme81\PSR4\Presentation\ArticleController::class,
                'show',
            ),
            new TargetMethod(
                new \Acme81\Attribute\Get(),
                \Acme81\PSR4\Presentation\ArticleController::class,
                'list',
            ),
            new TargetMethod(
                new \Acme81\Attribute\Post(),
                \Acme81\PSR4\Presentation\ArticleController::class,
                'new',
            ),
        ];

        $actual = Attributes::filterTargetMethods(
            Attributes::predicateForAttributeInstanceOf(\Acme81\Attribute\Route::class),
        );

        $this->assertEquals($expected, $actual);
    }

    public function testFilterTargetParameters(): void
    {
        $actual = Attributes::filterTargetParameters(
            Attributes::predicateForAttributeInstanceOf(ParameterA::class),
        );

        $this->assertEquals([
            [ new ParameterA("my parameter label"), 'Acme\PSR4\Presentation\ArticleController::aMethod(myParameter)' ],
            [
                new ParameterA('my yet another parameter label'),
                'Acme\PSR4\Presentation\ArticleController::aMethod(yetAnotherParameter)',
            ],
        ], $this->collectParameters($actual));
    }

    public function testFilterTargetProperties(): void
    {
        $actual = Attributes::filterTargetProperties(
            Attributes::predicateForAttributeInstanceOf(SchemaAttribute::class),
        );

        $this->assertEquals([
            [ new Boolean(), 'Acme\PSR4\ActiveRecord\Article::active' ],
            [ new Text(), 'Acme\PSR4\ActiveRecord\Article::body' ],
            [ new Id(), 'Acme\PSR4\ActiveRecord\Article::id' ],
            [ new Serial(), 'Acme\PSR4\ActiveRecord\Article::id' ],
            [ new Varchar(80, unique: true), 'Acme\PSR4\ActiveRecord\Article::slug' ],
            [ new Varchar(80), 'Acme\PSR4\ActiveRecord\Article::title' ],
        ], $this->collectProperties($actual));
    }

    public function testForClass(): void
    {
        $forClass = Attributes::forClass(ArticleController::class);

        $this->assertEquals([
            new Resource('articles'),
        ], $forClass->classAttributes);

        $this->assertEquals([
            'list' => [ new Route("/articles", 'GET', 'articles:list') ],
            'show' => [ new Route("/articles/{id}", 'GET', 'articles:show') ],
            'aMethod' => [ new Route("/articles/method/", 'GET', 'articles:method') ],
        ], $forClass->methodsAttributes);
    }

    /**
     * @template T of object
     *
     * @param array<TargetClass<T>> $targets
     *
     * @return array<array{T, class-string}>
     */
    private function collectClasses(array $targets): array
    {
        $methods = [];

        foreach ($targets as $target) {
            $methods[] = [ $target->attribute, $target->name ];
        }

        usort($methods, fn($a, $b) => $a[1] <=> $b[1]);

        return $methods;
    }

    /**
     * @template T of object
     *
     * @param array<TargetMethod<T>> $targets
     *
     * @return array<array{T, string}>
     */
    private function collectMethods(array $targets): array
    {
        $methods = [];

        foreach ($targets as $target) {
            $methods[] = [ $target->attribute, "$target->class::$target->name" ];
        }

        usort($methods, fn($a, $b) => $a[1] <=> $b[1]);

        return $methods;
    }

    /**
     * @template T of object
     *
     * @param TargetParameter<T>[] $targets
     *
     * @return array<array{T, string}>
     */
    private function collectParameters(array $targets): array
    {
        $parameters = [];

        foreach ($targets as $target) {
            $parameters[] = [ $target->attribute, "$target->class::$target->method($target->name)" ];
        }

        usort($parameters, fn($a, $b) => $a[1] <=> $b[1]);

        return $parameters;
    }

    /**
     * @template T of object
     *
     * @param TargetProperty<T>[] $targets
     *
     * @return array<array{T, string}>
     */
    private function collectProperties(array $targets): array
    {
        $properties = [];

        foreach ($targets as $target) {
            $properties[] = [ $target->attribute, "$target->class::$target->name" ];
        }

        usort($properties, fn($a, $b) => $a[1] <=> $b[1]);

        return $properties;
    }
}
