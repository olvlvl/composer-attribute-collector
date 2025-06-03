<?php

namespace olvlvl\ComposerAttributeCollector;

/**
 * @internal
 * @readonly
 */
class ParameterAttributeCollector
{
    public function __construct(
        private Logger $log
    ) {
    }

    /**
     * @return array<TransientTargetMethodParameter>
     */
    public function collectAttributes(\ReflectionFunctionAbstract $reflectionFunctionAbstract): array // TODO: change to
    {
        $targets = [];

        foreach ($reflectionFunctionAbstract->getParameters() as $parameter) {
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

            foreach ($parameter->getAttributes() as $attribute) {
                $this->log->debug("Found attribute {$attribute->getName()} on $paramLabel");

                $targets[] = new TransientTargetMethodParameter(
                    $attribute->getName(),
                    $attribute->getArguments(),
                    $functionName,
                    $parameterName
                );
            }
        }

        return $targets;
    }
}
