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

    /**
     * @var array<class-string, array{
     *     int,
     *     array<array{ class-string, array<int|string, mixed> }>,
     *     array<array{ class-string, array<int|string, mixed>, non-empty-string }>
     * }>
     *     Where _key_ is a class and _value is an array where `0` is a timestamp, `1` is an array of class attributes,
     *     and `2` is an array of method attributes.
     */
    private array $state;

    public function __construct(
        private ClassAttributeCollector $classAttributeCollector,
        private Datastore $datastore,
        private IOInterface $io,
    ) {
        /** @phpstan-ignore-next-line */
        $this->state = $this->datastore->get(self::KEY);
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

        foreach ($classMap as $class => $filepath) {
            $filterClasses[$class] = true;
            [ $timestamp, $classAttributes, $methodAttributes ] = $this->state[$class] ?? [ 0, [], [] ];

            $mtime = filemtime($filepath);

            if ($timestamp < $mtime) {
                $this->io->debug("Refresh attributes of class '$class' in '$filepath' ($timestamp < $mtime)");
                [ $classAttributes, $methodAttributes ] = $classAttributeCollector->collectAttributes($class);
                $this->state[$class] = [ time(), $classAttributes, $methodAttributes ];
            }

            $collector->addClassAttributes($classAttributes, $class);
            $collector->addMethodAttributes($methodAttributes, $class);
        }

        /**
         * Classes might have been removed, we need to filter according to the classes found.
         */
        $this->state = array_filter(
            $this->state,
            static fn(string $k): bool => $filterClasses[$k] ?? false,
            ARRAY_FILTER_USE_KEY
        );

        $this->datastore->set(self::KEY, $this->state);

        return $collector;
    }
}
