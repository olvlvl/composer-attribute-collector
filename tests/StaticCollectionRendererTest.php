<?php

namespace tests\olvlvl\ComposerAttributeCollector;

use Acme85\Attribute\WithClosure;
use Acme85\PSR4\SampleWithClosure;
use olvlvl\ComposerAttributeCollector\StaticCollectionRenderer;
use olvlvl\ComposerAttributeCollector\TransientCollection;
use olvlvl\ComposerAttributeCollector\TransientTargetProperty;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\TestCase;

final class StaticCollectionRendererTest extends TestCase
{
    #[RequiresPhp(">= 8.5")]
    public function testShouldFailOnClosureAsArgument()
    {
        $collector = new TransientCollection();
        $collector->addPropertyAttributes(WithClosure::class, [
            new TransientTargetProperty(WithClosure::class, [
                'name' => static function ($str) {
                    return strtoupper($str);
                }
            ], SampleWithClosure::class),
        ]);

        $this->expectExceptionMessageMatches("/PHP 8\\.5 Closures in constant expressions are not supported by the 'static' strategy/");

        StaticCollectionRenderer::render($collector);
    }
}
