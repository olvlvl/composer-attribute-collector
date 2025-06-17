<?php

namespace Acme81\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class ParameterA
{
    public function __construct(
        public string $label = ''
    ) {
    }
}
