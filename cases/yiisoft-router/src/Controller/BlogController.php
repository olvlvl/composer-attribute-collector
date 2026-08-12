<?php

namespace App\Controller;

use Yiisoft\Router\Attribute\Get;
use Yiisoft\Router\Attribute\Post;
use Yiisoft\Router\Group;

#[Group('/blog')]
final class BlogController
{
    #[Get('/posts', name: 'blog.posts')]
    public function list(): never
    {
        throw new \BadMethodCallException();
    }

    #[Post('/posts', name: 'blog.posts.create')]
    public function create(): never
    {
        throw new \BadMethodCallException();
    }
}
