<?php

namespace tests\olvlvl\ComposerAttributeCollector;

use Error;
use olvlvl\ComposerAttributeCollector\Collection;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class CollectionTest extends TestCase
{
    public function testInstantiationFailureIsCaptured(): void
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

        try {
            $collection->findTargetClasses(\Acme\Attribute\Permission::class);
            $this->fail("Expected failure");
        } catch (RuntimeException $e) {
            $this->assertEquals(
                "An error occurred while instantiating attribute Acme\Attribute\Permission on class Acme\PSR4\DeleteMenu",
                $e->getMessage()
            );

            $this->assertInstanceOf(Error::class, $e->getPrevious());
        }

        try {
            $collection->findTargetMethods(\Acme\Attribute\Route::class);
            $this->fail("Expected failure");
        } catch (RuntimeException $e) {
            $this->assertEquals(
                "An error occurred while instantiating attribute Acme\Attribute\Route on method Acme\PSR4\Presentation\ArticleController::list",
                $e->getMessage()
            );

            $this->assertInstanceOf(Error::class, $e->getPrevious());
        }

        try {
            $collection->forClass(\Acme\PSR4\DeleteMenu::class);
            $this->fail("Expected failure");
        } catch (RuntimeException $e) {
            $this->assertEquals(
                "An error occurred while instantiating attribute Acme\Attribute\Permission on class Acme\PSR4\DeleteMenu",
                $e->getMessage()
            );

            $this->assertInstanceOf(Error::class, $e->getPrevious());
        }

        try {
            $collection->forClass(\Acme\PSR4\Presentation\ArticleController::class);
            $this->fail("Expected failure");
        } catch (RuntimeException $e) {
            $this->assertEquals(
                "An error occurred while instantiating attribute Acme\Attribute\Route on method Acme\PSR4\Presentation\ArticleController::list",
                $e->getMessage()
            );

            $this->assertInstanceOf(Error::class, $e->getPrevious());
        }
    }
}
