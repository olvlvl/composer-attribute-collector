<?php

namespace Acme81\PSR4\Presentation;

use Acme81\Attribute\Get;
use Acme81\Attribute\Method;
use Acme81\Attribute\Post;
use Acme81\Attribute\Route;

#[Route('/articles')]
class ArticleController
{
    #[Route('/:id', method: Method::GET)]
    public function show()
    {
    }

    #[Get]
    public function list()
    {
    }

    #[Post]
    public function new()
    {
    }
}
