<?php

/*
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\olvlvl\ComposerAttributeCollector\Filter;

use Composer\IO\IOInterface;
use olvlvl\ComposerAttributeCollector\Filter\IgnorePathFilter;
use PHPUnit\Framework\TestCase;

final class IgnorePathFilterTest extends TestCase
{
    /**
     * @dataProvider provideFilterMatching
     */
    public function testFilterMatching(string $filepath, string $class, array  $paths): void
    {
        $io = $this->createMock(IOInterface::class);
        $io
            ->expects($this->never())
            ->method('debug');

        $filter = new IgnorePathFilter('/app', $paths);

        $actual = $filter->filter($filepath, $class, $io);

        $this->assertTrue($actual);
    }

    public function provideFilterMatching(): array
    {
        return [
            [ "/app/not-vendor/symfony/cache/Traits/RedisCluster5Proxy.php", "RedisCluster5Proxy", ['vendor/symfony/cache/Traits'] ],
            [ "/app/vendor/symfony/routing/Route.php", "Route", ['vendor/symfony/cache/Traits'] ],
        ];
    }

    /**
     * @dataProvider provideFilterNotMatching
     */
    public function testFilterNotMatching(string $filepath, string $class, array $paths): void
    {
        $io = $this->getMockBuilder(IOInterface::class)->getMock();
        $io
            ->expects($this->once())
            ->method('debug')
            ->with($this->stringStartsWith("Discarding '$class' because its path matches"));

        $filter = new IgnorePathFilter('/app', $paths);

        $actual = $filter->filter($filepath, $class, $io);

        $this->assertFalse($actual);
    }

    public function provideFilterNotMatching(): array
    {
        return [
            [ "/app/vendor/symfony/cache/Traits/RedisCluster5Proxy.php", "RedisCluster5Proxy", ['vendor/symfony/cache/Traits'] ],
            [ "/app/absolute/Test.php", "Test", ['/app/absolute'] ],
        ];
    }
}
