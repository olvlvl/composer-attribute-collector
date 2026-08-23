<?php

namespace Acme85\PSR4;

use Acme85\Attribute\WithClosure;

class SampleWithClosure
{
    #[WithClosure(static function ($str) {
        return strtoupper($str);
    })]
    public string $name;
}
