<?php

namespace Acme\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Post extends Route
{
    public function __construct(
        string $pattern = '',
        ?string $id = null
    ) {
        parent::__construct($pattern, 'POST', $id);
    }
}
