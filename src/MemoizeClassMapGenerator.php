<?php

namespace olvlvl\ComposerAttributeCollector;

use Composer\ClassMapGenerator\ClassMapGenerator;
use DirectoryIterator;
use RuntimeException;

use function array_filter;
use function array_merge;
use function file_exists;
use function filemtime;
use function is_dir;
use function is_int;

use const ARRAY_FILTER_USE_KEY;

/**
 * @internal
 */
class MemoizeClassMapGenerator
{
    private const KEY = 'classmap';

    /**
     * @var array<non-empty-string, array{ array<class-string, non-empty-string>, array<non-empty-string, int> }>
     *     Where _key_ is a directory or file path and _value_ an array
     *     where `0` is a class map, and `1` is a map of file paths to their mtimes.
     */
    private array $state;

    /**
     * @var array<string, bool>
     */
    private array $paths;

    public function __construct(
        private readonly Datastore $datastore,
        private readonly Logger $log,
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

        foreach ($this->state as [$map]) {
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
        [ , $cachedFileMtimes ] = $this->state[$path] ?? [ [], [] ];

        if ($this->shouldUpdate($path, $cachedFileMtimes)) {
            $inner = new ClassMapGenerator();
            $inner->avoidDuplicateScans();
            $inner->scanPaths($path, $excluded);
            $map = $inner->getClassMap()->getMap();

            $fileMtimes = [];
            foreach ($map as $filepath) {
                $mtime = filemtime($filepath);
                assert(is_int($mtime));
                $fileMtimes[$filepath] = $mtime;
            }

            $this->state[$path] = [ $map, $fileMtimes ];
        }
    }

    /**
     * @param array<non-empty-string, int> $cachedFileMtimes
     */
    private function shouldUpdate(string $path, array $cachedFileMtimes): bool
    {
        if (!$cachedFileMtimes) {
            return true;
        }

        $maxCachedMtime = 0;

        foreach ($cachedFileMtimes as $filepath => $cachedMtime) {
            if ($cachedMtime > $maxCachedMtime) {
                $maxCachedMtime = $cachedMtime;
            }

            if (!file_exists($filepath)) {
                $this->log->debug("Refresh class map: file '$filepath' was removed");

                return true;
            }

            $currentMtime = filemtime($filepath);

            assert(is_int($currentMtime));

            if ($currentMtime !== $cachedMtime) {
                $diff = $currentMtime - $cachedMtime;
                $this->log->debug("Refresh class map: file '$filepath' changed ($diff sec)");

                return true;
            }
        }

        return $this->hasNewerDirectoryMtime($path, $maxCachedMtime);
    }

    private function hasNewerDirectoryMtime(string $path, int $maxCachedMtime): bool
    {
        $dirMtime = filemtime($path);

        assert(is_int($dirMtime));

        if ($dirMtime > $maxCachedMtime) {
            $diff = $dirMtime - $maxCachedMtime;
            $this->log->debug("Refresh class map for path '$path' ($diff sec ago)");

            return true;
        }

        // Could be a file referenced as a class map, we don't want to iterate over that.
        if (!is_dir($path)) {
            return false;
        }

        foreach (new DirectoryIterator($path) as $di) {
            if ($di->isDir() && !$di->isDot()) {
                if ($this->hasNewerDirectoryMtime($di->getPathname(), $maxCachedMtime)) {
                    return true;
                }
            }
        }

        return false;
    }
}
