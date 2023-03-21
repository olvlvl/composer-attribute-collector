<?php

namespace tests\olvlvl\ComposerAttributeCollector;

use olvlvl\ComposerAttributeCollector\MemoizeClassMapGenerator;
use PHPUnit\Framework\TestCase;

use function file_put_contents;
use function realpath;
use function sleep;
use function time;
use function touch;
use function var_dump;

final class MemoizeClassMapGeneratorTest extends TestCase
{
    private const DIR = __DIR__ . '/sandbox-memoize-classmap/';

    protected function setUp(): void
    {
        parent::setUp();

        clear_directory(self::DIR);
    }

    public function testMemoize(): void
    {
        $map = $this->map();
        $this->assertEmpty($map);

        self::write(
            "a.php",
            <<<PHP
            <?php

            namespace App;

            #[\Acme\Attribute\Handler]
            class A {
            }
            PHP
        );

        $map = $this->map();
        $this->assertEquals([
            'App\A' => self::DIR . 'a.php',
        ], $map);

        self::write(
            "b.php",
            <<<PHP
            <?php

            namespace App;

            #[\Acme\Attribute\Handler]
            class B {
            }
            PHP
        );

        // Because the modified time is a unix timestamp, we need the modified time to be the next second
        touch(self::DIR, time() + 1);

        $map = $this->map();
        $this->assertEquals([
            'App\A' => self::DIR . 'a.php',
            'App\B' => self::DIR . 'b.php',
        ], $map);
    }

    private static function write(string $name, string $data): void
    {
        file_put_contents(self::DIR . $name, $data);
    }

    /**
     * @return array<class-string, string>
     */
    private static function map(): array
    {
        $generator = new MemoizeClassMapGenerator(__DIR__ . '/../.composer-attribute-collector');
        $generator->scanPaths(self::DIR);

        return $generator->getMap();
    }
}
