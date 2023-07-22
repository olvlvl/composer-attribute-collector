<?php

/*
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\olvlvl\ComposerAttributeCollector\Filter;

use Composer\IO\IOInterface;
use olvlvl\ComposerAttributeCollector\Filter\PathFilter;
use PHPUnit\Framework\TestCase;

final class PathFilterTest extends TestCase
{
    private PathFilter $filter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filter = new PathFilter(
            include: [
                "/app/src",
                "/app/vendor",
            ],
            exclude: [
                "/app/src/Excluded.php",
                "/app/vendor/symfony/cache/Traits",
            ],
        );
    }

    /**
     * @dataProvider provideFilter
     *
     * @param class-string $class
     */
    public function testFilter(string $filepath, string $class, bool $expected): void
    {
        $io = $this->getMockBuilder(IOInterface::class)->getMock();

        if ($expected) {
            $io
                ->expects($this->never())
                ->method('debug');
        } else {
            $io
                ->expects($this->once())
                ->method('debug')
                ->with($this->stringStartsWith("Discarding '$class'"));
        }

        $actual = $this->filter->filter($filepath, $class, $io);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array<array{ non-empty-string, string, bool }>
     */
    public function provideFilter(): array
    {
        return [

            [ "/app/src/some/file.php", "SampleClass", true ],
            [ "/app/src/Excluded.php", "SampleClass", false ],
            [ "/app/vendor/symfony/routing/Route.php", "Route", true ],
            [ "/app/vendor/symfony/cache/Traits/RedisCluster5Proxy.php", "RedisCluster5Proxy", false ],
            [ "vendor/symfony/cache/Traits/RedisCluster5Proxy.php", "RedisCluster5Proxy", false ],
            [ "symfony/cache/Traits/RedisCluster5Proxy.php", "RedisCluster5Proxy", false ],
            [ "/vendor/symfony/routing/Route.php", "Route", false ],

        ];
    }
}
