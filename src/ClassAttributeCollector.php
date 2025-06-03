<?php

namespace olvlvl\ComposerAttributeCollector;

use Attribute;
use Composer\IO\IOInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;

/**
 * @internal
 */
class ClassAttributeCollector
{
    public function __construct(
        private IOInterface $io,
    ) {
    }

    /**
     * @param class-string $class
     *
     * @return array{
     *     array<TransientTargetClass>,
     *     array<TransientTargetMethod>,
     *     array<TransientTargetProperty>,
     *     array<array<TransientTargetMethodParameter>>,
     * }
     *
     * @throws ReflectionException
     */
    public function collectAttributes(string $class): array
    {
        $classReflection = new ReflectionClass($class);

        if (self::isAttribute($classReflection)) {
            return [ [], [], [], [] ];
        }

        $classAttributes = [];
        $attributes = $classReflection->getAttributes();

        foreach ($attributes as $attribute) {
            if (self::isAttributeIgnored($attribute)) {
                continue;
            }

            $this->io->debug("Found attribute {$attribute->getName()} on $class");

            $classAttributes[] = new TransientTargetClass(
                $attribute->getName(),
                $attribute->getArguments(),
            );
        }

        $methodAttributes = [];
        $methodParameterAttributes = [];

        foreach ($classReflection->getMethods() as $methodReflection) {
            $this->collectMethodAndParameterAttributes(
                $class,
                $methodReflection,
                $methodAttributes,
                $methodParameterAttributes,
            );
        }

        $propertyAttributes = [];

        foreach ($classReflection->getProperties() as $propertyReflection) {
            foreach ($propertyReflection->getAttributes() as $attribute) {
                if (self::isAttributeIgnored($attribute)) {
                    continue;
                }

                $property = $propertyReflection->name;
                assert($property !== '');

                $this->io->debug("Found attribute {$attribute->getName()} on $class::$property");

                $propertyAttributes[] = new TransientTargetProperty(
                    $attribute->getName(),
                    $attribute->getArguments(),
                    $property,
                );
            }
        }

        return [ $classAttributes, $methodAttributes, $propertyAttributes, $methodParameterAttributes ];
    }

    /**
     * Determines if a class is an attribute.
     *
     * @param ReflectionClass<object> $classReflection
     */
    private static function isAttribute(ReflectionClass $classReflection): bool
    {
        foreach ($classReflection->getAttributes() as $attribute) {
            if ($attribute->getName() === Attribute::class) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ReflectionAttribute<object> $attribute
     */
    private static function isAttributeIgnored(ReflectionAttribute $attribute): bool
    {
        static $ignored = [
            \ReturnTypeWillChange::class => true,
        ];

        return isset($ignored[$attribute->getName()]); // @phpstan-ignore offsetAccess.nonOffsetAccessible
    }

    /**
     * @param array<TransientTargetMethod> $methodAttributes
     * @param array<array<TransientTargetMethodParameter>> $methodParameterAttributes
     * @return void
     */
    private function collectMethodAndParameterAttributes(string $class, \ReflectionMethod $methodReflection, array &$methodAttributes, array &$methodParameterAttributes): void
    {
        $parameterAttributeCollector = new ParameterAttributeCollector($this->io);
        foreach ($methodReflection->getAttributes() as $attribute) {
            if (self::isAttributeIgnored($attribute)) {
                continue;
            }

            $method = $methodReflection->name;

            $this->io->debug("Found attribute {$attribute->getName()} on $class::$method");

            $methodAttributes[] = new TransientTargetMethod(
                $attribute->getName(),
                $attribute->getArguments(),
                $method,
            );
        }

        $parameterAttributes = $parameterAttributeCollector->collectAttributes($methodReflection);
        if ($parameterAttributes !== []) {
            $methodParameterAttributes[] = $parameterAttributes;
        }
    }
}
