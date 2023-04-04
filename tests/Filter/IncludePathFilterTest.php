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
    public function testFilterMatching(string $filepath, string $class, array $paths): void
    {
        $io = $this->createMock(IOInterface::class);
        $io
            ->expects($this->never())
            ->method('debug');

        $filter = new IncludePathFilter('/app', $paths);

        $actual = $filter->filter($filepath, $class, $io);

        $this->assertTrue($actual);
    }

    public function provideFilterMatching(): array
    {
        return [
            // special case if no include paths provided, include all
            ['/app/tests/Test.php', 'Test', []],

            // otherwide only included paths are allowed
            ['/app/src/Test.php', 'Test', ['src/']],
            ['/app/src/Test.php', 'Test', ['/app/src/']],
        ];
    }

    /**
     * @dataProvider provideFilterNotMatching
     */
    public function testFilterNotMatching(string $filepath, string $class, array $paths): void
    {
        $io = $this->createMock(IOInterface::class);
        $io
            ->expects($this->once())
            ->method('debug')
            ->with($this->stringStartsWith("Discarding '$class' because its path is not on include list"));

        $filter = new IncludePathFilter('/app', $paths);

        $actual = $filter->filter($filepath, $class, $io);

        self::assertFalse($actual);
    }

    public function provideFilterNotMatching(): array
    {
        return [
            ['/app/tests/Test.php', 'Test', ['src/']],
            ['/app/tests/src/Test.php', 'Test', ['src/']],
        ];
    }
}
