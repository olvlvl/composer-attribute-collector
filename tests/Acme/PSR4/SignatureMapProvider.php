<?php

namespace Acme\PSR4;

use Acme\Attribute\AutowiredService;

#[AutowiredService(factory: '@Acme\PSR4\SignatureMap\SignatureMapProviderFactory::create')]
interface SignatureMapProvider
{
}
