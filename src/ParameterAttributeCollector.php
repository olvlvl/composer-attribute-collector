<?php

namespace olvlvl\ComposerAttributeCollector;

use Composer\IO\IOInterface;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;

/**
 * @internal
 */
class ParameterAttributeCollector
{
    public function __construct(
        private IOInterface $io
    ) {
    }

    /**
     * @return array<TransientTargetMethodParameter>
     */
    public function collectAttributes(\ReflectionFunctionAbstract $reflectionFunctionAbstract): array
    {
        $funcParameterAttributes = [];
        foreach ($reflectionFunctionAbstract->getParameters() as $parameter) {
            $attributes = $parameter->getAttributes();
            $functionName = $reflectionFunctionAbstract->name;
            $parameterName = $parameter->name;
            assert($functionName !== '');
            assert($parameterName !== '');

            $paramLabel = '';
            if ($reflectionFunctionAbstract instanceof \ReflectionMethod) {
                $paramLabel = $reflectionFunctionAbstract->class . '::' . $functionName . '(' . $parameterName . ')';
            } elseif ($reflectionFunctionAbstract instanceof \ReflectionFunction) {
                $paramLabel = $functionName . '(' . $parameterName . ')';
            }

            foreach ($attributes as $attribute) {
                $this->io->debug("Found attribute {$attribute->getName()} on $paramLabel");

                $funcParameterAttributes[] = new TransientTargetMethodParameter(
                    $attribute->getName(),
                    $attribute->getArguments(),
                    $functionName,
                    $parameterName
                );
            }
        }

        return $funcParameterAttributes;
    }
}
