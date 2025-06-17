<?php

namespace Acme\Attribute;

use Attribute;

#[Attribute(flags: Attribute::TARGET_CLASS)]
final class AutowiredService
{
    /**
     * @param true|list<class-string>|class-string $as
     */
    public function __construct(
        public ?string $name = null,
        public ?string $factory = null,
        public bool|array|string $as = true,
    ) {
    }
}
