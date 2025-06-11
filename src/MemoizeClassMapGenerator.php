<?php

namespace olvlvl\ComposerAttributeCollector;

use Composer\ClassMapGenerator\ClassMapGenerator;
use DirectoryIterator;
use RuntimeException;

use function array_filter;
use function array_merge;
use function filemtime;
use function is_dir;
use function is_int;
use function time;

use const ARRAY_FILTER_USE_KEY;

/**
 * @internal
 */
class MemoizeClassMapGenerator
{
    private const KEY = 'classmap';

    /**
     * @var array<non-empty-string, array{ int, array<class-string, non-empty-string> }>
     *     Where _key_ is a directory path and _value_ an array
     *     where `0` is a timestamp, and `1` is an array
     *     where _key_ is a class and _value_ its pathname.
     */
    private array $state;

    /**
     * @var array<string, bool>
     */
    private array $paths;

    public function __construct(
        private Datastore $datastore,
        private Logger $log,
    ) {
        /** @phpstan-ignore-next-line */
        $this->state = $this->datastore->get(self::KEY);
    }

    /**
     * @return array<class-string, non-empty-string>
     *     Where _key_ is a class and _value_ its path.
     */
    public function getMap(): array
    {
        /**
         * Paths might have been removed, we need to filter according to the paths provided during {@link scanPaths()}
         */
        $this->state = array_filter(
            $this->state,
            fn(string $k): bool => $this->paths[$k] ?? false,
            ARRAY_FILTER_USE_KEY
        );

        $this->datastore->set(self::KEY, $this->state);

        $maps = [];

        foreach ($this->state as [, $map]) {
            $maps[] = $map;
        }

        return array_merge(...$maps);
    }

    /**
     * Iterate over all files in the given directory searching for classes
     *
     * @param non-empty-string $path
     *     The path to search in.
     * @param non-empty-string|null $excluded
     *     Regex that matches file paths to be excluded from the classmap
     *
     * @throws RuntimeException When the path is neither an existing file nor directory
     */
    public function scanPaths(string $path, ?string $excluded = null): void
    {
        $this->paths[$path] = true;
        [ $timestamp ] = $this->state[$path] ?? [ 0 ];

        if ($this->shouldUpdate($timestamp, $path)) {
            $inner = new ClassMapGenerator();
            $inner->avoidDuplicateScans();
            $inner->scanPaths($path, $excluded);
            $map = $inner->getClassMap()->getMap();

            $this->state[$path] = [ time(), $map ];
        }
    }

    private function shouldUpdate(int $timestamp, string $path): bool
    {
        if (!$timestamp) {
            return true;
        }

        $mtime = filemtime($path);

        assert(is_int($mtime));

        if ($timestamp < $mtime) {
            $diff = $mtime - $timestamp;
            $this->log->debug("Refresh class map for path '$path' ($diff sec ago)");

            return true;
        }

        // Could be a file referenced as a class map, we don't want to iterate over that.
        if (!is_dir($path)) {
            return false;
        }

        foreach (new DirectoryIterator($path) as $di) {
            if ($di->isDir() && !$di->isDot()) {
                if ($this->shouldUpdate($timestamp, $di->getPathname())) {
                    return true;
                }
            }
        }

        return false;
    }
}
