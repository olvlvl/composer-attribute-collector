<?php

namespace Acme\PSR4;

use Acme\Attribute\Handler;
use IteratorAggregate;

#[Handler]
class IncompatibleSignature implements IteratorAggregate
{
    public function getIterator(string $invalid): void
    {
        // TODO: Implement getIterator() method.
    }
}
