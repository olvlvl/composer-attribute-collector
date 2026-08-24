<?php

namespace tests\olvlvl\ComposerAttributeCollector\Filter;

use olvlvl\ComposerAttributeCollector\Filter\ContentFilter;
use olvlvl\ComposerAttributeCollector\Logger;
use PHPUnit\Framework\Attributes\DataProvider;
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

    #[DataProvider("provideAttribute")]
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

    public static function provideAttribute(): array
    {
        return [

            [ "AttributeFullyQualifiedWithoutArgument" ],
            [ "AttributeFullyQualifiedWithArgument" ],
            [ "AttributeImportedWithoutArgument" ],
            [ "AttributeImportedWithArgument" ],

        ];
    }

    #[DataProvider("provideClass")]
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

    public static function provideClass(): array
    {
        return [

            [ "ClassWithoutAttribute", false ],
            [ "ClassWithAttribute", true ],

        ];
    }
}
