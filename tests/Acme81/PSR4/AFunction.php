<?php

namespace Acme81\PSR4;

use Acme81\Attribute\ParameterA;

function aFunc(
    #[ParameterA("my function parameter label")]
    $aParameter
) {
}
