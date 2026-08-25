<?php

namespace tests\olvlvl\ComposerAttributeCollector\Factory;

use olvlvl\ComposerAttributeCollector\Factory\FilterFactory;
use olvlvl\ComposerAttributeCollector\Filter\Chain;
use olvlvl\ComposerAttributeCollector\Filter\ClassFilter;
use olvlvl\ComposerAttributeCollector\Filter\ContentFilter;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

use function iterator_to_array;

final class FilterFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $filter = FilterFactory::create();

        $this->assertInstanceOf(Chain::class, $filter);

        $filters = iterator_to_array((new ReflectionProperty(Chain::class, 'filters'))->getValue($filter));

        $this->assertCount(2, $filters);
        $this->assertInstanceOf(ContentFilter::class, $filters[0]);
        $this->assertInstanceOf(ClassFilter::class, $filters[1]);
    }
}
