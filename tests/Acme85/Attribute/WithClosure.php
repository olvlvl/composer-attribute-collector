<?php

namespace Acme85\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class WithClosure
{
    public function __construct(\Closure $closure)
    {
    }
}
