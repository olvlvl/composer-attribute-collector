<?php

namespace Acme\Filter;

use olvlvl\ComposerAttributeCollector\Filter\ClassFilter;
use olvlvl\ComposerAttributeCollector\Logger;
use PHPUnit\Framework\TestCase;
use tests\olvlvl\ComposerAttributeCollector\FakeLogger;

final class ClassFilterTest extends TestCase
{
    private string $path;
    private ClassFilter $sut;
    private FakeLogger $log;

    protected function setUp(): void
    {
        // The path doesn't matter.
        $this->path = uniqid();
        $this->sut = new ClassFilter();
        $this->log = new FakeLogger();

        parent::setUp();
    }

    public function testWithClass(): void
    {
        $actual = $this->sut->filter($this->path, \Acme\PSR4\CreateMenuHandler::class, $this->log);

        $this->assertTrue($actual);
    }

    public function testWithInterface(): void
    {
        $actual = $this->sut->filter($this->path, \Acme\PSR4\SignatureMapProvider::class, $this->log);

        $this->assertTrue($actual);
    }

    public function testWithTrait(): void
    {
        $actual = $this->sut->filter($this->path, \Acme\PSR4\SampleTrait::class, $this->log);

        $this->assertFalse($actual);
    }

    public function testDiscardOnError(): void
    {
        $log = $this->createMock(Logger::class);
        $log->expects($this->once())
            ->method('warning')
            ->with($this->stringStartsWith("Discarding 'Acme\PSR4\MissingInterface"));

        $actual = $this->sut->filter($this->path, \Acme\PSR4\MissingInterface::class, $log);

        $this->assertFalse($actual);
    }
}
