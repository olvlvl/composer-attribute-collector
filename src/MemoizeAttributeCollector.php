<?php

namespace olvlvl\ComposerAttributeCollector;

use Composer\IO\IOInterface;
use ReflectionException;

use function array_filter;
use function filemtime;

use const ARRAY_FILTER_USE_KEY;

/**
 * @internal
 */
class MemoizeAttributeCollector
{
    private const KEY = 'attributes';

    public function __construct(
        private ClassAttributeCollector $classAttributeCollector,
        private Datastore $datastore,
        private IOInterface $io,
    )
    {
    }

    /**
     * @param array<class-string, string> $classMap
     *     Where _key_ is a class and _value_ its pathname.
     *
     * @throws ReflectionException
     */
    public function collectAttributes(array $classMap): Collector
    {
        $filterClasses = [];
        $classAttributeCollector = $this->classAttributeCollector;
        $collector = new Collector();

        $cacheState = $this->datastore->get(self::KEY);

        foreach ($classMap as $class => $filepath) {
            $filterClasses[$class] = true;

            $classCacheState = $cacheState[$class] ?? [];
            $timestamp = $classCacheState[0] ?? 0;
            $transientClass = self::classCacheStateToTransientClass($classCacheState);

            $mtime = $transientClass !== null ? filemtime($filepath) : 0;

            if ($transientClass === null || $timestamp < $mtime) {
                $this->io->debug("Refresh attributes of class '$class' in '$filepath' ($timestamp < $mtime)");

                $timestamp = time();
                $transientClass = $classAttributeCollector->collectAttributes($class);
            }

            $cacheState[$class] = [
                $timestamp,
                $transientClass->classAttributes,
                $transientClass->methodAttributes,
                $transientClass->propertyAttributes,
            ];

            $collector->addClassAttributes($class, $transientClass->classAttributes);
            $collector->addMethodAttributes($class, $transientClass->methodAttributes);
            $collector->addTargetProperties($class, $transientClass->propertyAttributes);
        }

        /**
         * Classes might have been removed, we need to filter according to the classes found.
         */
        $cacheState = array_filter(
            $cacheState,
            static fn(string $k): bool => $filterClasses[$k] ?? false,
            ARRAY_FILTER_USE_KEY
        );

        $this->datastore->set(self::KEY, $cacheState);

        return $collector;
    }


    /**
     * @param array{
     *     0?: int,
     *     1?: array<TransientTargetClass>,
     *     2?: array<TransientTargetMethod>,
     *     3?: array<TransientTargetProperty>,
     * } $classCacheState
     *     Where _key_ is a class and _value is an array where:
     *     - `0` is a timestamp
     *     - `1` is an array of class attributes
     *     - `2` is an array of method attributes
     *     - `3` is an array of property attributes
     * @return TransientClass|null
     */
    private static function classCacheStateToTransientClass(array $classCacheState): ?TransientClass
    {
        // if any collection is missing in the cache entry, throw away the entry as a whole
        if (!isset(
            $classCacheState[1],
            $classCacheState[2],
            $classCacheState[3],
        )) {
            return null;
        }

        return new TransientClass(
            classAttributes: $classCacheState[1],
            methodAttributes: $classCacheState[2],
            propertyAttributes: $classCacheState[3],
        );
    }
}
