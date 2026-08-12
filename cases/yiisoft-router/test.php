<?php

use Yiisoft\Router\ComposerAttributeCollector\AttributeRoutesProvider;

require 'vendor/autoload.php';

$provider = new AttributeRoutesProvider();
$routes = $provider->getRoutes();

var_dump($routes);

count($routes) === 1
    or throw new RuntimeException("Expected 1 route group, got " . count($routes));

$routes[0] instanceof Yiisoft\Router\Group
    or throw new RuntimeException("Expected Group instance");

$routes[0]->getData('prefix') === '/blog'
    or throw new RuntimeException("Expected prefix '/blog'");
