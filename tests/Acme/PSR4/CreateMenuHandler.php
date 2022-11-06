<?php

/*
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Acme\PSR4;

use Acme\Attribute\Handler;

#[Handler]
final class CreateMenuHandler
{
    public function __invoke(CreateMenu $command): void
    {
    }
}
