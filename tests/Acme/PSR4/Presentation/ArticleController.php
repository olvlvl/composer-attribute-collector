<?php

/*
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Acme\PSR4\Presentation;

use Acme\Attribute\Resource;
use Acme\Attribute\Route;

#[Resource("articles")]
final class ArticleController
{
    #[Route(method: 'GET', id: 'articles:list', pattern: "/articles")]
    public function list(): void
    {
    }

    #[Route(id: 'articles:show', pattern: "/articles/{id}", method: 'GET')]
    public function show(int $id): void
    {
    }
}
