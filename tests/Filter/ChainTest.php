<?php

/*
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\olvlvl\ComposerAttributeCollector\Filter;

use olvlvl\ComposerAttributeCollector\Filter;
use olvlvl\ComposerAttributeCollector\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use tests\olvlvl\ComposerAttributeCollector\FakeLogger;

final class ChainTest extends TestCase
{
    private const FILEPATH = "vendor/symfony/cache/Traits/RedisCluster5Proxy.php";
    private const CLASSNAME = "RedisCluster5Proxy";

    private Logger $log;
    private Filter|MockObject $ok;
    private Filter|MockObject $ko;
    private Filter|MockObject $no;

    protected function setUp(): void
    {
        parent::setUp();

        $this->log = new FakeLogger();

        $ok = $this->ok = $this->getMockBuilder(Filter::class)->getMock();
        $ok->method('filter')->with(self::FILEPATH, self::CLASSNAME, $this->log)->willReturn(true);

        $ko = $this->ko = $this->getMockBuilder(Filter::class)->getMock();
        $ko->method('filter')->with(self::FILEPATH, self::CLASSNAME, $this->log)->willReturn(false);

        $no = $this->no = $this->getMockBuilder(Filter::class)->getMock();
        $no->expects($this->never())->method('filter')->with(self::FILEPATH, self::CLASSNAME, $this->log);
    }

    public function testFilter_OkIfNoFalse()
    {
        $chain = new Filter\Chain([ $this->ok, $this->ok ]);

        $actual = $chain->filter(self::FILEPATH, self::CLASSNAME, $this->log);

        $this->assertTrue($actual);
    }

    public function testFilter_False()
    {
        $chain = new Filter\Chain([ $this->ko ]);

        $actual = $chain->filter(self::FILEPATH, self::CLASSNAME, $this->log);

        $this->assertFalse($actual);
    }

    public function testFilter_FalseBefore()
    {
        $chain = new Filter\Chain([ $this->ko, $this->no ]);

        $actual = $chain->filter(self::FILEPATH, self::CLASSNAME, $this->log);

        $this->assertFalse($actual);
    }

    public function testFilter_FalseAfter()
    {
        $chain = new Filter\Chain([ $this->ok, $this->ko, $this->no ]);

        $actual = $chain->filter(self::FILEPATH, self::CLASSNAME, $this->log);

        $this->assertFalse($actual);
    }
}
