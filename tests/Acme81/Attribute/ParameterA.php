<?php

namespace Acme81\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class ParameterA
{
    public string $label;
    public function __construct(
        string $label = ''
    ) {
        $this->label = $label;
    }
}
