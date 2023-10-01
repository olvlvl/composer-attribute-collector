<?php

namespace Acme\PSR4;

use Acme\PSR4\Routing\UrlTrait;
use olvlvl\ComposerAttributeCollector\InheritsAttributes;

#[InheritsAttributes]
class InheritedAttributeSample
{
    use UrlTrait;
}
