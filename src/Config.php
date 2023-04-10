<?php

namespace olvlvl\ComposerAttributeCollector;

use Composer\Factory;
use Composer\PartialComposer;
use InvalidArgumentException;
use RuntimeException;

use function array_merge;
use function dirname;
use function is_string;
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
    public const EXTRA_IGNORE_PATHS = 'ignore-paths';

    public const BUILTIN_IGNORE_PATHS = [

        // https://github.com/olvlvl/composer-attribute-collector/issues/4
        "{vendor}/symfony/cache/Traits"

    ];

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

        /** @var array{ ignore-paths?: non-empty-string[] } $extra */
        $extra = $composer->getPackage()->getExtra()[self::EXTRA] ?? [];

        $ignorePaths = self::expandPaths(
            array_merge($extra[self::EXTRA_IGNORE_PATHS] ?? [], self::BUILTIN_IGNORE_PATHS),
            $vendorDir,
            $rootDir
        );

        return new self(
            attributesFile: "$vendorDir/attributes.php",
            ignorePaths: $ignorePaths,
        );
    }

    /**
     * @param non-empty-string $attributesFile
     *     Absolute path to the `attributes.php` file.
     * @param string[] $ignorePaths
     *     Paths that should be ignored for attributes collection.
     */
    public function __construct(
        public string $attributesFile,
        public array $ignorePaths,
    ) {
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
