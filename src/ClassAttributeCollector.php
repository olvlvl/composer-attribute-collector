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
     * @return TransientClass
     * @throws ReflectionException
     */
    public function collectAttributes(string $class): TransientClass
    {
        $classReflection = new ReflectionClass($class);

        if (self::isAttribute($classReflection)) {
            return new TransientClass([], [], []);
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

        foreach ($classReflection->getMethods() as $methodReflection) {
            foreach ($methodReflection->getAttributes() as $attribute) {
                if (self::isAttributeIgnored($attribute)) {
                    continue;
                }

                $method = $methodReflection->name;
                assert($method !== '');

                $this->io->debug("Found attribute {$attribute->getName()} on $class::$method");

                $methodAttributes[] = new TransientTargetMethod(
                    $attribute->getName(),
                    $attribute->getArguments(),
                    $method,
                );
            }
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

        return new TransientClass(
            classAttributes: $classAttributes,
            methodAttributes: $methodAttributes,
            propertyAttributes: $propertyAttributes,
        );
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

        return isset($ignored[$attribute->getName()]);
    }
}
