<?php

namespace Acme85\PSR4;

use Acme85\Attribute\WithClosure;

class SampleWithClosure
{
    public const MAGIC_STRING = "I'M A CLOSURE";

    #[WithClosure(static function () {
        return self::MAGIC_STRING;
    })]
    public string $name;
}
