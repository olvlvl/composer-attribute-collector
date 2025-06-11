<?php

namespace tests\olvlvl\ComposerAttributeCollector;

use Acme\PSR4\ActiveRecord\Article;
use Acme\PSR4\CreateMenu;
use Acme\PSR4\CreateMenuHandler;
use Acme\PSR4\Presentation\ArticleController;
use Acme\PSR4\SubscriberA;
use Attribute;
use olvlvl\ComposerAttributeCollector\ClassAttributeCollector;
use olvlvl\ComposerAttributeCollector\TransientTargetClass;
use olvlvl\ComposerAttributeCollector\TransientTargetMethod;
use olvlvl\ComposerAttributeCollector\TransientTargetProperty;
use PHPUnit\Framework\TestCase;
use ReflectionException;

final class ClassAttributeCollectorTest extends TestCase
{
    private ClassAttributeCollector $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new ClassAttributeCollector(new FakeLogger());
    }

    /**
     * @dataProvider provideCollectAttributes
     *
     * @param class-string $class
     * @param array<int|string, mixed> $expected
     *
     * @throws ReflectionException
     */
    public function testCollectAttributes(string $class, array $expected): void
    {
        $actual = $this->sut->collectAttributes($class);

        $this->assertEquals($expected, $actual);
    }

    /** @phpstan-ignore-next-line */
    public static function provideCollectAttributes(): array
    {
        return [

            [
                Attribute::class,
                [
                    [],
                    [],
                    [],
                ]
            ],

            [
                CreateMenu::class,
                [
                    [
                        new TransientTargetClass('Acme\Attribute\Permission', [ 'is_admin' ]),
                        new TransientTargetClass('Acme\Attribute\Permission', [ 'can_create_menu' ]),
                    ],
                    [],
                    [],
                ]
            ],

            [
                CreateMenuHandler::class,
                [
                    [
                        new TransientTargetClass('Acme\Attribute\Handler', []),
                    ],
                    [],
                    [],
                ]
            ],

            [
                ArticleController::class,
                [
                    [
                        new TransientTargetClass('Acme\Attribute\Resource', [ "articles" ]),
                    ],
                    [
                        new TransientTargetMethod(
                            'Acme\Attribute\Route',
                            [ 'method' => 'GET', 'id' => 'articles:list', 'pattern' => "/articles" ],
                            'list',
                        ),
                        new TransientTargetMethod(
                            'Acme\Attribute\Route',
                            [ 'id' => 'articles:show', 'pattern' => "/articles/{id}", 'method' => 'GET' ],
                            'show',
                        ),
                    ],
                    [],
                ]
            ],

            [
                SubscriberA::class,
                [
                    [],
                    [
                        new TransientTargetMethod('Acme\Attribute\Subscribe', [], 'onEventA'),
                    ],
                    [],
                ]
            ],

            [
                Article::class,
                [
                    [
                        new TransientTargetClass('Acme\Attribute\ActiveRecord\Index', [ 'active' ]),
                    ],
                    [
                    ],
                    [
                        new TransientTargetProperty('Acme\Attribute\ActiveRecord\Id', [], 'id'),
                        new TransientTargetProperty('Acme\Attribute\ActiveRecord\Serial', [], 'id'),
                        new TransientTargetProperty('Acme\Attribute\ActiveRecord\Varchar', [ 80 ], 'title'),
                        new TransientTargetProperty('Acme\Attribute\ActiveRecord\Varchar', [ 80, 'unique' => true ], 'slug'),
                        new TransientTargetProperty('Acme\Attribute\ActiveRecord\Text', [], 'body'),
                        new TransientTargetProperty('Acme\Attribute\ActiveRecord\Boolean', [], 'active'),
                    ],
                ]
            ],

        ];
    }
}
