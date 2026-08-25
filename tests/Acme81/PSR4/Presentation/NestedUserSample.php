<?php

namespace Acme81\PSR4\Presentation;

use Acme81\Attribute\Get;
use Acme81\Attribute\Method;
use Acme81\Attribute\Post;
use Acme81\Attribute\Route;
use Acme81\Attribute\SampleNested;
use Acme81\Attribute\SampleNestedValue;

#[SampleNested(new SampleNestedValue(1))]
class NestedUserSample
{
}
