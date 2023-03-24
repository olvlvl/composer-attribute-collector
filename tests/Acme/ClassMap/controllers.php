<?php

/*
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Acme\Presentation;

use Acme\Attribute\Get;
use Acme\Attribute\Route;

#[Route('/images')]
final class ImageController
{
    #[Get]
    protected function list(): void
    {
    }

    #[Get("/{id}")]
    private function show(int $id): void
    {
    }
}

#[Route('/files')]
final class FileController
{
    #[Get]
    public function list(): void
    {
    }

    #[Get('/{id}')]
    public function show(int $id): void
    {
    }
}
