<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
class HelloController
{
    #[Route('/hello', name: 'hello', methods: [ 'GET' ])]
    public function index(): Response
    {
        throw new \BadMethodCallException();
    }
}
