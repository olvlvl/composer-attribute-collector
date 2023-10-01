<?php

namespace Acme\PSR4\Routing;

use Acme\Attribute\Routing\UrlGetter;

trait UrlTrait
{
    #[UrlGetter]
    public function get_url(): string
    {
        return '/url';
    }
}
