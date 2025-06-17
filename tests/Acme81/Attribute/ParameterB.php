<?php

namespace Acme81\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class ParameterB
{
    public function __construct(
        public string $label = '',
        public string $moreData = ''
    ) {
    }
}
