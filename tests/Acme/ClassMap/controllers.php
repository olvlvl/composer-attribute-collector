<?php

/*
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Acme\Presentation;

use Acme\Attribute\Route;

final class ImageController
{
    #[Route("/images")]
    protected function list(): void
    {
    }

    #[Route("/images/{id}")]
    private function show(int $id): void
    {
    }
}

final class FileController
{
    #[Route("/files")]
    public function list(): void
    {
    }

    #[Route("/files/{id}")]
    public function show(int $id): void
    {
    }
}
