<?php

namespace tests\olvlvl\ComposerAttributeCollector\Logger;

use Composer\IO\IOInterface;
use olvlvl\ComposerAttributeCollector\Logger\ComposerLogger;
use PHPUnit\Framework\TestCase;

final class ComposerLoggerTest extends TestCase
{
    public function testDebug(): void
    {
        $message = uniqid();
        $io = $this->createMock(IOInterface::class);
        $io->expects(self::once())->method('debug')->with($message);

        $log =  new ComposerLogger($io);
        $log->debug($message);
    }

    public function testWarning(): void
    {
        $message = uniqid();
        $io = $this->createMock(IOInterface::class);
        $io->expects(self::once())->method('warning')->with($message);

        $log =  new ComposerLogger($io);
        $log->warning($message);
    }

    public function testError(): void
    {
        $message = uniqid();
        $io = $this->createMock(IOInterface::class);
        $io->expects(self::once())->method('error')->with($message);

        $log =  new ComposerLogger($io);
        $log->error($message);
    }
}
