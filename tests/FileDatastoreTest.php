<?php

namespace tests\olvlvl\ComposerAttributeCollector;

use olvlvl\ComposerAttributeCollector\Datastore\FileDatastore;
use olvlvl\ComposerAttributeCollector\Logger;
use olvlvl\ComposerAttributeCollector\Plugin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function file_put_contents;

final class FileDatastoreTest extends TestCase
{
    private const DIR = __DIR__ . '/sandbox/';
    private const KEY = 'file-datastore';

    /**
     * @var MockObject&Logger
     */
    private MockObject|Logger $log;
    private FileDatastore $sut;

    protected function setUp(): void
    {
        $this->log = $this->createMock(Logger::class);
        $this->sut = new FileDatastore(self::DIR, $this->log);

        parent::setUp();
    }

    public function testValidUnserialize(): void
    {
        $str = 'a:3:{i:0;i:2;i:1;i:3;i:2;i:5;}';
        $expected = [ 2, 3, 5 ];
        $this->log
            ->expects($this->never())
            ->method('warning')
            ->withAnyParameters();

        self::write($str);

        $actual = $this->sut->get(self::KEY);

        $this->assertEquals($expected, $actual);
    }

    public function testUnserializeWithMissingEnum(): void
    {
        $str = 'a:1:{i:0;E:12:"Priority:TOP";}';
        $expected = [];
        $this->log
            ->expects($this->atLeastOnce()) // PHP 8.2 also complains that the class 'Priority' is not found
            ->method('warning')
            ->with($this->stringStartsWith("Unable to unserialize cache item"));

        self::write($str);

        $actual = $this->sut->get(self::KEY);

        $this->assertEquals($expected, $actual);
    }

    public function testUnserializeWithMissingClass(): void
    {
        $this->markTestSkipped("Don't know how to catch missing classes yet");

        $str = 'a:1:{i:0;O:8:"Priority":1:{s:8:"priority";i:1;}}';
        $expected = [];
        $this->log
            ->expects($this->atLeastOnce())
            ->method('warning')
            ->with($this->stringStartsWith("Unable to unserialize cache item"));

        self::write($str);

        $actual = $this->sut->get(self::KEY);

        $this->assertEquals($expected, $actual);
    }

    private static function write(string $str): void
    {
        $filename = self::DIR . 'v' . Plugin::VERSION_MAJOR . '-' . Plugin::VERSION_MINOR . '-' . self::KEY;

        file_put_contents($filename, $str);
    }
}
