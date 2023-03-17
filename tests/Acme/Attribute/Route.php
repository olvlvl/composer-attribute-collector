<?php

/*
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Acme\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class Route
{
    /**
     * @param string|string[] $method
     */
    public function __construct(
        public string $pattern,
        public string|array $method = 'GET',
        public ?string $id = null,
    ) {
    }
}
