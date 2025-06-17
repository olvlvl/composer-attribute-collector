<?php

namespace olvlvl\ComposerAttributeCollector;

use Throwable;

use function array_filter;
use function filemtime;

use const ARRAY_FILTER_USE_KEY;

/**
 * @internal
 */
class MemoizeAttributeCollector
{
    private const KEY = 'attributes';

    /**
     * @var array<class-string, array{
     *     int,
     *     array<TransientTargetClass>,
     *     array<TransientTargetMethod>,
     *     array<TransientTargetProperty>,
     *     array<TransientTargetParameter>,
     * }>
     *     Where _key_ is a class and _value_ is an array where:
     *     - `0` is a timestamp
     */
    private array $state;

    public function __construct(
        private ClassAttributeCollector $classAttributeCollector,
        private Datastore $datastore,
        private Logger $log,
    ) {
        /** @phpstan-ignore-next-line */
        $this->state = $this->datastore->get(self::KEY);
    }

    /**
     * @param array<class-string, string> $classMap
     *     Where _key_ is a class and _value_ its pathname.
     */
    public function collectAttributes(array $classMap): TransientCollection
    {
        $filterClasses = [];
        $classAttributeCollector = $this->classAttributeCollector;
        $collector = new TransientCollection();

        foreach ($classMap as $class => $filepath) {
            $filterClasses[$class] = true;

            [
                $timestamp,
                $classAttributes,
                $methodAttributes,
                $propertyAttributes,
                $parameterAttributes,
            ] = $this->state[$class] ?? [ 0, [], [], [], [] ];

            $mtime = filemtime($filepath);

            if ($timestamp < $mtime) {
                if ($timestamp) {
                    $diff = $mtime - $timestamp;
                    $this->log->debug("Refresh attributes of class '$class' in '$filepath' ($diff sec ago)");
                } else {
                    $this->log->debug("Collect attributes of class '$class' in '$filepath'");
                }

                try {
                    [
                        $classAttributes,
                        $methodAttributes,
                        $propertyAttributes,
                        $parameterAttributes,
                    ] = $classAttributeCollector->collectAttributes($class);
                } catch (Throwable $e) {
                    $this->log->error(
                        "Attribute collection failed for $class: {$e->getMessage()}",
                    );
                }

                $this->state[$class] = [
                    time(),
                    $classAttributes,
                    $methodAttributes,
                    $propertyAttributes,
                    $parameterAttributes,
                ];
            }

            if (count($classAttributes)) {
                $collector->addClassAttributes($class, $classAttributes);
            }
            if (count($methodAttributes)) {
                $collector->addMethodAttributes($class, $methodAttributes);
            }
            if (count($parameterAttributes)) {
                $collector->addParameterAttributes($class, $parameterAttributes);
            }
            if (count($propertyAttributes)) {
                $collector->addTargetProperties($class, $propertyAttributes);
            }
        }

        /**
         * Classes might have been removed, we need to filter entries according to the classes found.
         */
        $this->state = array_filter(
            $this->state,
            static fn(string $k): bool => $filterClasses[$k] ?? false,
            ARRAY_FILTER_USE_KEY,
        );

        $this->datastore->set(self::KEY, $this->state);

        return $collector;
    }
}
