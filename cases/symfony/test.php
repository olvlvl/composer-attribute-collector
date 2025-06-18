<?php

use App\Controller\HelloController;
use olvlvl\ComposerAttributeCollector\Attributes;
use olvlvl\ComposerAttributeCollector\TargetClass;
use olvlvl\ComposerAttributeCollector\TargetMethod;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

require 'vendor/autoload.php';

#
# Target Class
#
$actual = Attributes::findTargetClasses(AsController::class);
$expected = [
    new TargetClass(new AsController(), HelloController::class),
];

var_dump($actual);

$actual == $expected or throw new RuntimeException("Target classes don't match");

#
# Target Method
#

$actual = Attributes::findTargetMethods(Route::class);
$expected = [
    new TargetMethod(
        new Route('/hello', name: 'hello', methods: [ 'GET' ]),
        HelloController::class,
        'index',
    ),
];

var_dump($actual);

$actual == $expected or throw new RuntimeException("Target methods don't match");
