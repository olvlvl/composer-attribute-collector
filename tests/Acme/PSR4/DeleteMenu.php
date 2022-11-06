<?php

/*
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Acme\PSR4;

use Acme\Attribute\Permission;

#[Permission('is_admin')]
#[Permission('can_delete_menu')]
final class DeleteMenu
{
    public function __construct(
        public int $id
    ) {
    }
}
