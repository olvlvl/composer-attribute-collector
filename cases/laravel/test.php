<?php

use App\Attributes\SampleAttribute;
use olvlvl\ComposerAttributeCollector\Attributes;
use olvlvl\ComposerAttributeCollector\TargetClass;

require 'vendor/autoload.php';

$actual = Attributes::findTargetClasses(SampleAttribute::class);
$expected = [
    new TargetClass(new SampleAttribute(), App\Models\User::class),
    new TargetClass(new SampleAttribute(), App\Providers\AppServiceProvider::class),
];

var_dump($actual);

$actual == $expected or throw new \RuntimeException("Target classes don't match expected");
