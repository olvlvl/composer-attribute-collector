<?php

namespace Acme85\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class WithClosure
{
    public function __construct(
        public \Closure $closure
    ) {
    }
}
