<?php

/*
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\olvlvl\ComposerAttributeCollector\Filter;

use Composer\IO\IOInterface;
use olvlvl\ComposerAttributeCollector\Filter\IncludePathFilter;
use PHPUnit\Framework\TestCase;

final class IncludePathFilterTest extends TestCase
{
    /**
     * @dataProvider provideFilterMatching
     */
    public function testFilterMatching(string $filepath, string $class, string $cwd, array $paths): void
    {
        $io = $this->createMock(IOInterface::class);
        $io
            ->expects($this->never())
            ->method('debug');

        $filter = new IncludePathFilter($cwd, $paths);

        $actual = $filter->filter($filepath, $class, $io);

        $this->assertTrue($actual);
    }

    public function provideFilterMatching(): array
    {
        $cwd = '/path/to/project/root';
        return [
            // special case if no include paths provided, include all
            ["$cwd/tests/Test.php", 'Test', $cwd, []],

            // otherwide only included paths are allowed
            ["$cwd/src/Test.php", 'Test', $cwd, ['src/']],
            ["/some/absolute/path/src/Test.php", 'Test', $cwd, ['/some/absolute/path/src/']],
        ];
    }

    /**
     * @dataProvider provideFilterNotMatching
     */
    public function testFilterNotMatching(string $filepath, string $class, string $cwd, array $paths): void
    {
        $io = $this->createMock(IOInterface::class);
        $io
            ->expects($this->once())
            ->method('debug')
            ->with($this->stringStartsWith("Discarding '$class' because its path is not on include list"));

        $filter = new IncludePathFilter($cwd, $paths);

        $actual = $filter->filter($filepath, $class, $io);

        self::assertFalse($actual);
    }

    public function provideFilterNotMatching(): array
    {
        $cwd = '/path/to/project/root';
        return [
            ["$cwd/tests/Test.php", 'Test', $cwd, ['src/']],
            ["$cwd/tests/src/Test.php", 'Test', $cwd, ['src/']],
        ];
    }
}
