<?php

namespace Acme81\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Route
{
    /**
     * @param Method|Method[] $method
     */
    public function __construct(
        public string $pattern,
        public Method|array $method = Method::GET,
        public ?string $id = null,
    ) {
    }
}
