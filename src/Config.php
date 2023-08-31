<?php

namespace olvlvl\ComposerAttributeCollector;

use Composer\Factory;
use Composer\PartialComposer;
use Composer\Util\Platform;
use InvalidArgumentException;
use RuntimeException;

use function array_map;
use function dirname;
use function filter_var;
use function implode;
use function is_string;
use function preg_quote;
use function realpath;
use function str_ends_with;
use function str_starts_with;
use function strlen;

use const DIRECTORY_SEPARATOR;

/**
 * @readonly
 * @internal
 */
final class Config
{
    public const EXTRA = 'composer-attribute-collector';
    public const EXTRA_INCLUDE = 'include';
    public const EXTRA_EXCLUDE = 'exclude';
    public const ENV_USE_CACHE = 'COMPOSER_ATTRIBUTE_COLLECTOR_USE_CACHE';

    /**
     * If a path starts with this placeholder, it is replaced with the absolute path to the vendor directory.
     */
    public const VENDOR_PLACEHOLDER = '{vendor}';

    public static function from(PartialComposer $composer): self
    {
        $vendorDir = $composer->getConfig()->get('vendor-dir');

        if (!is_string($vendorDir) || !$vendorDir) {
            throw new RuntimeException("Unable to determine vendor directory");
        }

        $composerFile = Factory::getComposerFile();
        $rootDir = realpath(dirname($composerFile));

        if (!$rootDir) {
            throw new RuntimeException("Unable to determine root directory");
        }

        $rootDir .= DIRECTORY_SEPARATOR;

        /** @var array{ include?: non-empty-string[], exclude?: non-empty-string[] } $extra */
        $extra = $composer->getPackage()->getExtra()[self::EXTRA] ?? [];

        $include = self::expandPaths($extra[self::EXTRA_INCLUDE] ?? [], $vendorDir, $rootDir);
        $exclude = self::expandPaths($extra[self::EXTRA_EXCLUDE] ?? [], $vendorDir, $rootDir);

        $useCache = filter_var(Platform::getEnv(self::ENV_USE_CACHE), FILTER_VALIDATE_BOOL);

        return new self(
            $vendorDir,
            attributesFile: "$vendorDir/attributes.php",
            include: $include,
            exclude: $exclude,
            useCache: $useCache,
        );
    }

    /**
     * @readonly
     * @var non-empty-string|null
     */
    public ?string $excludeRegExp;

    /**
     * @param non-empty-string $attributesFile
     *     Absolute path to the `attributes.php` file.
     * @param non-empty-string[] $include
     *     Paths that should be included to attributes collection.
     * @param non-empty-string[] $exclude
     *     Paths that should be excluded from attributes collection.
     * @param bool $useCache
     *     Whether a cache should be used during the process.
     */
    public function __construct(
        public string $vendorDir,
        public string $attributesFile,
        public array $include,
        public array $exclude,
        public bool $useCache,
    ) {
        $this->excludeRegExp = count($exclude) ? self::compileExclude($this->exclude) : null;
    }

    /**
     * @param non-empty-string[] $exclude
     *
     * @return non-empty-string
     */
    private static function compileExclude(array $exclude): string
    {
        $regexp = implode('|', array_map(fn (string $path) => preg_quote($path), $exclude));

        return "($regexp)";
    }

    /**
     * @param non-empty-string[] $paths
     * @param non-empty-string $vendorDir
     * @param non-empty-string $rootDir
     *
     * @return non-empty-string[]
     */
    private static function expandPaths(array $paths, string $vendorDir, string $rootDir): array
    {
        if (str_ends_with($vendorDir, DIRECTORY_SEPARATOR)) {
            throw new InvalidArgumentException("vendorDir must not end with a directory separator, given: $vendorDir");
        }

        if (!str_ends_with($rootDir, DIRECTORY_SEPARATOR)) {
            throw new InvalidArgumentException("rootDir must end with a directory separator, given: $rootDir");
        }

        $expanded = [];

        foreach ($paths as $path) {
            if (str_starts_with($path, self::VENDOR_PLACEHOLDER)) {
                $path = $vendorDir . substr($path, strlen(self::VENDOR_PLACEHOLDER));
            } else {
                $path = $rootDir . $path;
            }

            $expanded[] = $path;
        }

        return $expanded;
    }
}
