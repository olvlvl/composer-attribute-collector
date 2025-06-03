<?php

namespace Acme81\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class ParameterB
{
    public string $label;
    public string $moreData;
    public function __construct(
        string $label = '',
        string $moreData = ''
    ) {
        $this->label = $label;
        $this->moreData = $moreData;
    }
}
