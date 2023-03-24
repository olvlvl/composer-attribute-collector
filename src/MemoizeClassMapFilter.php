<?php

namespace olvlvl\ComposerAttributeCollector;

use Closure;
use Composer\IO\IOInterface;

use function array_filter;
use function filemtime;
use function is_int;
use function time;

use const ARRAY_FILTER_USE_KEY;

/**
 * @internal
 */
class MemoizeClassMapFilter
{
    private const KEY = 'filtered';

    /**
     * @var array<non-empty-string, array{ int, bool }>
     */
    private array $state;

    public function __construct(
        private Datastore $datastore,
        private IOInterface $io,
    ) {
        /** @phpstan-ignore-next-line */
        $this->state = $this->datastore->get(self::KEY);
    }

    /**
     * @param array<class-string, non-empty-string> $classMap
     *     Where _key_ is a class and _value_ its pathname.
     * @param Closure(class-string, non-empty-string): bool $filter
     *
     * @return array<class-string, non-empty-string>
     */
    public function filter(array $classMap, Closure $filter): array
    {
        $filtered = [];
        $paths = [];

        foreach ($classMap as $class => $pathname) {
            $paths[$pathname] = true;
            [ $timestamp, $keep ] = $this->state[$pathname] ?? [ 0, false ];

            $mtime = filemtime($pathname);

            assert(is_int($mtime));

            if ($timestamp < $mtime) {
                $this->io->debug("Refresh filtered for '$pathname' ($timestamp < $mtime)");
                $keep = $filter($class, $pathname);
                $this->state[$pathname] = [ time(), $keep ];
            }

            if ($keep) {
                $filtered[$class] = $pathname;
            }
        }

        /**
         * Paths might have been removed, we need to filter according to the paths found.
         */
        $this->state = array_filter(
            $this->state,
            static fn(string $k): bool => $paths[$k] ?? false,
            ARRAY_FILTER_USE_KEY
        );

        $this->datastore->set(self::KEY, $this->state);

        return $filtered;
    }
}
