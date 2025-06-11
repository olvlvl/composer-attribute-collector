<?php

/*
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\olvlvl\ComposerAttributeCollector\Filter;

use olvlvl\ComposerAttributeCollector\Filter\ContentFilter;
use olvlvl\ComposerAttributeCollector\Logger;
use PHPUnit\Framework\MockObject\MockObject as MockObjectAlias;
use PHPUnit\Framework\TestCase;

final class ContentFilterTest extends TestCase
{
    private ContentFilter $sut;
    private MockObjectAlias|Logger $log;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new ContentFilter();
        $this->log = $this->getMockBuilder(Logger::class)->getMock();
    }

    /**
     * @dataProvider provideAttribute
     */
    public function testAttribute(string $case): void
    {
        $this->log->expects($this->once())
            ->method('debug')
            ->with("Discarding '$case' because it looks like an attribute");

        $actual = $this->sut->filter(
            __DIR__ . "/ContentFilterCases/$case.php",
            $case,
            $this->log
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
    public function testClass(string $case, bool $expected): void
    {
        $this->log->expects($this->never())
            ->method('debug')
            ->with($this->anything());

        $actual = $this->sut->filter(
            __DIR__ . "/ContentFilterCases/$case.php",
            $case,
            $this->log
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
