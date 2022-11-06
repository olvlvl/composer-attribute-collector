<?php

/*
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace olvlvl\ComposerAttributeCollector;

use Composer\Composer;

/**
 * @internal
 */
class AutoloadsBuilder
{
    /**
     * @return array{
     *     'psr-0': array<string, array<string>>,
     *     'psr-4': array<string, array<string>>,
     *     'classmap': array<int, string>,
     *     'files': array<string, string>,
     *     'exclude-from-classmap': array<int, string>,
     * }
     */
    public function buildAutoloads(Composer $composer): array
    {
        $package = $composer->getPackage();
        $packages = $composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages();
        $generator = $composer->getAutoloadGenerator();
        $packageMap = $generator->buildPackageMap($composer->getInstallationManager(), $package, $packages);

        return $generator->parseAutoloads($packageMap, $package);
    }
}
