<?php

namespace olvlvl\ComposerAttributeCollector;

use Closure;
use Composer\ClassMapGenerator\ClassMapGenerator;
use RuntimeException;

use function array_merge;
use function array_values;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function filemtime;
use function is_dir;
use function is_string;
use function mkdir;
use function preg_replace;
use function serialize;
use function str_starts_with;
use function strlen;
use function substr;
use function unserialize;

use const DIRECTORY_SEPARATOR;

/**
 * *Note:* We have to extend {@link ClassMapGenerator} because there's no interface available.
 */
class MemoizeClassMapGenerator
{
    private const CACHE_DIR = '.composer-attribute-collector';

    /**
     * @var array<string, array<class-string, non-empty-string>>
     *     Where _key_ is a directory and _value_ is an array where _key_ is a class and _value_ its path.
     */
    public array $mapByDir = [];
    private string $cachePath;

    public function __construct(
        private string $basePath
    ) {
        $this->cachePath = $this->basePath . DIRECTORY_SEPARATOR . self::CACHE_DIR . DIRECTORY_SEPARATOR;

        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath);
        }
    }

    /**
     * @return array<class-string, non-empty-string>
     *     Where _key_ is a class and _value_ its path.
     */
    public function getMap(): array
    {
        return array_merge(...array_values($this->mapByDir));
    }

    /**
     * Iterate over all files in the given directory searching for classes
     *
     * @param string $path
     *     The path to search in.
     * @param non-empty-string|null $excluded
     *     Regex that matches file paths to be excluded from the classmap
     * @param 'classmap'|'psr-0'|'psr-4' $autoloadType
     *     Optional autoload standard to use mapping rules with the namespace instead of purely doing a classmap
     * @param string|null $namespace
     *     Optional namespace prefix to filter by, only for psr-0/psr-4 autoloading
     *
     * @throws RuntimeException When the path is neither an existing file nor directory
     */
    public function scanPaths(
        string $path,
        string $excluded = null,
        string $autoloadType = 'classmap',
        ?string $namespace = null
    ): void {
        $this->mapByDir[$path] = $this->get(
            $path,
            static function () use ($path, $excluded, $autoloadType, $namespace): array {
                $inner = new ClassMapGenerator();
                $inner->avoidDuplicateScans();
                $inner->scanPaths($path, $excluded, $autoloadType, $namespace);

                return $inner->getClassMap()->getMap();
            }
        );
    }

    /**
     * @param string $path
     * @param Closure $scan
     *
     * @return array<class-string, non-empty-string>
     *     Where _key_ is a class and _value_ its path.
     */
    private function get(string $path, Closure $scan): array
    {
        $key = $this->makeCacheKey($path);
        $filename = $this->cachePath . $key;
        $map = $this->cacheGet($filename, $path);

        if ($map) {
            return $map;
        }

        $map = $scan();

        $this->cacheSet($filename, $map);

        return $map;
    }

    private function makeCacheKey(string $path): string
    {
        $base = $this->basePath . DIRECTORY_SEPARATOR;

        if (str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }

        $key = preg_replace('/[^0-9A-Za-z]/', '-', $path);

        assert(is_string($key));

        return $key;
    }

    /**
     * @param string $filename
     * @param string $reference
     *
     * @return array<class-string, non-empty-string>|null
     *     Where _key_ is a class and _value_ is its path.
     */
    private function cacheGet(string $filename, string $reference): ?array
    {
        if (!file_exists($filename)) {
            return null;
        }

        $c = filemtime($filename);
        $r = filemtime($reference);

        if ($c === false || $r === false) {
            return null;
        }

        if ($r > $c) {
            return null;
        }

        $data = file_get_contents($filename);

        if ($data) {
            /** @phpstan-ignore-next-line */
            return unserialize($data);
        }

        return null;
    }

    /**
     * @param array<class-string, non-empty-string> $map
     *     Where _key_ is a class and _value_ is its path.
     */
    private function cacheSet(string $filename, array $map): void
    {
        file_put_contents($filename, serialize($map));
    }
}
