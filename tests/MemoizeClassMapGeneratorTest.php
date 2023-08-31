<?php

namespace tests\olvlvl\ComposerAttributeCollector;

use Composer\IO\NullIO;
use olvlvl\ComposerAttributeCollector\Datastore\FileDatastore;
use olvlvl\ComposerAttributeCollector\MemoizeClassMapGenerator;
use PHPUnit\Framework\TestCase;

use function file_exists;
use function file_put_contents;
use function time;
use function touch;
use function unlink;

final class MemoizeClassMapGeneratorTest extends TestCase
{
    private const DIR = __DIR__ . '/sandbox-memoize-classmap/';

    protected function setUp(): void
    {
        parent::setUp();

        $remove = [
            self::DIR . 'a.php',
            self::DIR . 'a/b/c/b.php',
        ];

        foreach ($remove as $filename) {
            if (file_exists($filename)) {
                unlink($filename);
            }
        }
    }

    public function testMemoize(): void
    {
        $map = $this->map(self::DIR);
        $this->assertEmpty($map);

        // check changes in the directory are detected
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

        $map = $this->map(self::DIR . 'a.php');
        $this->assertEquals([
            'App\A' => self::DIR . 'a.php',
        ], $map);

        // map again to test the code is not trying to iterate over the file like it's a directory
        $map = $this->map(self::DIR . 'a.php');
        $this->assertEquals([
            'App\A' => self::DIR . 'a.php',
        ], $map);

        // check changes in subdirectories are detected
        self::write(
            "a/b/c/b.php",
            <<<PHP
            <?php

            namespace App;

            #[\Acme\Attribute\Handler]
            class B {
            }
            PHP
        );

        $map = $this->map(self::DIR);
        $this->assertEquals([
            'App\A' => self::DIR . 'a.php',
            'App\B' => self::DIR . 'a/b/c/b.php',
        ], $map);
    }

    private static function write(string $name, string $data): void
    {
        file_put_contents(self::DIR . $name, $data);

        // Because the modified time granularity is a second, we need the set the time to the next second,
        // so that we don't have to use sleep().
        touch(self::DIR, time() + 1);
    }

    /**
     * @param non-empty-string $path
     *
     * @return array<class-string, non-empty-string>
     */
    private static function map(string $path): array
    {
        $io = new NullIO();
        $generator = new MemoizeClassMapGenerator(
            new FileDatastore(get_cache_dir(), $io),
            $io,
        );

        $generator->scanPaths($path);

        return $generator->getMap();
    }
}
