<?php

/*
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace olvlvl\ComposerAttributeCollector;

use Composer\ClassMapGenerator\ClassMapGenerator;
use Composer\Pcre\Preg;
use Composer\Util\Filesystem;
use Composer\Util\Platform;

use function count;
use function file_exists;
use function implode;
use function is_dir;
use function krsort;
use function preg_quote;
use function realpath;

/**
 * @internal
 */
class ClassMapBuilder
{
    /**
     * @param array{
     *     'psr-0': array<string, array<string>>,
     *     'psr-4': array<string, array<string>>,
     *     'classmap': array<int, string>,
     *     'exclude-from-classmap': array<int, string>,
     * } $autoloads
     *
     * @return array<class-string, non-empty-string>
     *     Where _key_ is a class and _value_ the absolute path of its file.
     */
    public function buildClassMap(array $autoloads): array
    {
        $excluded = $autoloads['exclude-from-classmap'];
        $classMapGenerator = new ClassMapGenerator();
        $classMapGenerator->avoidDuplicateScans();

        foreach ($autoloads['classmap'] as $dir) {
            // @phpstan-ignore-next-line
            $classMapGenerator->scanPaths($dir, self::buildExclusionRegex($dir, $excluded));
        }

        $namespacesToScan = [];

        // Scan the PSR-0/4 directories for class files, and add them to the class map
        foreach ([ 'psr-4', 'psr-0' ] as $psrType) {
            foreach ($autoloads[$psrType] as $namespace => $paths) {
                $namespacesToScan[$namespace][] = [ 'paths' => $paths, 'type' => $psrType ];
            }
        }

        krsort($namespacesToScan);

        $filesystem = new Filesystem();
        // @phpstan-ignore-next-line
        $basePath = $filesystem->normalizePath(realpath(realpath(Platform::getCwd())));

        foreach ($namespacesToScan as $namespace => $groups) {
            foreach ($groups as $group) {
                foreach ($group['paths'] as $dir) {
                    $dir = $filesystem->normalizePath(
                        $filesystem->isAbsolutePath($dir) ? $dir : $basePath . '/' . $dir
                    );
                    if (!is_dir($dir)) {
                        continue; // @codeCoverageIgnore
                    }

                    $classMapGenerator->scanPaths(
                        $dir,
                        // @phpstan-ignore-next-line
                        self::buildExclusionRegex($dir, $excluded),
                        $group['type'],
                        $namespace
                    );
                }
            }
        }

        return $classMapGenerator->getClassMap()->getMap();
    }

    /**
     * {@link \Composer\Autoload\AutoloadGenerator::buildExclusionRegex}
     */
    // @phpstan-ignore-next-line
    private static function buildExclusionRegex(string $dir, ?array $excluded): ?string
    {
        if (null === $excluded) {
            return null; //@codeCoverageIgnore
        }

        // filter excluded patterns here to only use those matching $dir
        // exclude-from-classmap patterns are all realpath'd, so we can only filter them if $dir exists so that
        //realpath($dir) will work if $dir does not exist, it should anyway not find anything there so no trouble
        if (file_exists($dir)) {
            // transform $dir in the same way that exclude-from-classmap patterns are transformed so we can match them
            // against each other
            // @phpstan-ignore-next-line
            $dirMatch = preg_quote(strtr(realpath($dir), '\\', '/'));
            foreach ($excluded as $index => $pattern) {
                // extract the constant string prefix of the pattern here,
                // until we reach a non-escaped regex special character
                $pattern = Preg::replace(
                    '{^(([^.+*?\[^\]$(){}=!<>|:\\\\#-]+|\\\\[.+*?\[^\]$(){}=!<>|:#-])*).*}',
                    '$1',
                    $pattern
                );
                // if the pattern is not a subset or superset of $dir, it is unrelated and we skip it
                if (!str_starts_with($pattern, $dirMatch) && !str_starts_with($dirMatch, $pattern)) {
                    unset($excluded[$index]);
                }
            }
        }

        return count($excluded) > 0 ? '{(' . implode('|', $excluded) . ')}' : null;
    }
}
