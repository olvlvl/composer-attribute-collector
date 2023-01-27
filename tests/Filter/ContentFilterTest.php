<?php

/*
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\olvlvl\ComposerAttributeCollector\Filter;

use Composer\IO\IOInterface;
use olvlvl\ComposerAttributeCollector\Filter\ContentFilter;
use PHPUnit\Framework\MockObject\MockObject as MockObjectAlias;
use PHPUnit\Framework\TestCase;

final class ContentFilterTest extends TestCase
{
    private ContentFilter $sut;
    private MockObjectAlias|IOInterface $io;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new ContentFilter();
        $this->io = $this->getMockBuilder(IOInterface::class)->getMock();
    }

    /**
     * @dataProvider provideAttribute
     */
    public function testAttribute(string $case)
    {
        $this->io->expects($this->once())
            ->method('debug')
            ->with("Discarding '$case' because it looks like an attribute");

        $actual = $this->sut->filter(
            __DIR__ . "/ContentFilterCases/$case.php",
            $case,
            $this->io
        );

        $this->assertFalse($actual);
    }

    public function provideAttribute(): array
    {
        return [

            [ "AttributeFullyQualifiedWithoutArgument" ],
            [ "AttributeFullyQualifiedWithArgument" ],
            [ "AttributeImportedWithoutArgument" ],
            [ "AttributeImportedWithArgument" ],

        ];
    }

    /**
     * @@dataProvider provideClass
     */
    public function testClass(string $case, bool $expected)
    {
        $this->io->expects($this->never())
            ->method('debug')
            ->with($this->anything());

        $actual = $this->sut->filter(
            __DIR__ . "/ContentFilterCases/$case.php",
            $case,
            $this->io
        );

        $this->assertEquals($expected, $actual);
    }

    public function provideClass(): array
    {
        return [

            [ "ClassWithoutAttribute", false ],
            [ "ClassWithAttribute", true ],

        ];
    }
}
