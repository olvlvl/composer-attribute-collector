<?php

namespace tests\olvlvl\ComposerAttributeCollector;

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
            self::DIR . 'modification.php',
            self::DIR . 'deletion.php',
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

    public function testMemoizeDetectsFileModification(): void
    {
        self::write("modification.php", "<?php\nclass ModificationA {}");

        $map = $this->map(self::DIR);
        $this->assertEquals(['ModificationA' => self::DIR . 'modification.php'], $map);

        self::write("modification.php", "<?php\nclass ModificationA {}\nclass ModificationB {}");

        $map = $this->map(self::DIR);
        $this->assertEquals([
            'ModificationA' => self::DIR . 'modification.php',
            'ModificationB' => self::DIR . 'modification.php',
        ], $map);
    }

    public function testMemoizeDetectsFileDeletion(): void
    {
        self::write("deletion.php", "<?php\nclass DeletionA {}");

        $map = $this->map(self::DIR);
        $this->assertEquals(['DeletionA' => self::DIR . 'deletion.php'], $map);

        unlink(self::DIR . 'deletion.php');

        $map = $this->map(self::DIR);
        $this->assertEmpty($map);
    }

    private static function write(string $name, string $data): void
    {
        static $offset = 0;
        $offset++;

        $file = self::DIR . $name;

        file_put_contents($file, $data);

        // Because the modified time granularity is a second, we use an incrementing offset
        // to guarantee distinct mtimes without sleep().
        touch($file, time() + $offset);
    }

    /**
     * @param non-empty-string $path
     *
     * @return array<class-string, non-empty-string>
     */
    private static function map(string $path): array
    {
        $log = new FakeLogger();
        $generator = new MemoizeClassMapGenerator(
            new FileDatastore(get_cache_dir(), $log),
            $log,
        );

        $generator->scanPaths($path);

        return $generator->getMap();
    }
}
