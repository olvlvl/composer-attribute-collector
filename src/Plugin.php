<?php

/*
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace olvlvl\ComposerAttributeCollector;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Util\Platform;
use olvlvl\ComposerAttributeCollector\Filter\ContentFilter;
use olvlvl\ComposerAttributeCollector\Filter\InterfaceFilter;
use olvlvl\ComposerAttributeCollector\Filter\PathFilter;
use ReflectionException;

use function array_merge;
use function file_put_contents;
use function is_string;
use function microtime;
use function spl_autoload_register;
use function sprintf;
use function var_export;

use const DIRECTORY_SEPARATOR;

/**
 * @internal
 */
final class Plugin implements PluginInterface, EventSubscriberInterface
{
    public const EXTRA = 'composer-attribute-collector';
    public const EXTRA_IGNORE_PATHS = 'ignore-paths';
    private const CACHE_DIR = '.composer-attribute-collector';
    private const PROBLEMATIC_PATHS = [
        // https://github.com/olvlvl/composer-attribute-collector/issues/4
        'symfony/cache/Traits'
    ];

    /**
     * @uses onPostAutoloadDump
     *
     * @codeCoverageIgnore
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'post-autoload-dump' => 'onPostAutoloadDump',
        ];
    }

    /**
     * @codeCoverageIgnore
     */
    public function activate(Composer $composer, IOInterface $io): void
    {
    }

    /**
     * @codeCoverageIgnore
     */
    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    /**
     * @codeCoverageIgnore
     */
    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }

    public static function onPostAutoloadDump(Event $event): void
    {
        $composer = $event->getComposer();
        $io = $event->getIO();
        $vendorDir = $composer->getConfig()->get('vendor-dir');
        assert(is_string($vendorDir));
        $filepath = "$vendorDir/attributes.php";

        $start = microtime(true);
        $io->write('<info>Generating attributes file</info>');
        self::dump($event->getComposer(), $io, $filepath);
        $elapsed = self::renderElapsedTime($start);
        $io->write("<info>Generated attributes file in $elapsed</info>");
    }

    /**
     * @throws ReflectionException
     */
    public static function dump(
        Composer $composer,
        IOInterface $io,
        string $filepath,
        AutoloadsBuilder $autoloadsBuilder = null,
        ClassMapBuilder $classMapBuilder = null
    ): void {
        $datastore = self::buildDefaultDatastore();
        $autoloadsBuilder ??= new AutoloadsBuilder();
        $classMapGenerator = new MemoizeClassMapGenerator($datastore, $io);
        $classMapBuilder ??= new ClassMapBuilder($classMapGenerator);
        $classMapFilter = new MemoizeClassMapFilter($datastore, $io);
        $attributeCollector = new MemoizeAttributeCollector(new ClassAttributeCollector($io), $datastore, $io);

        $start = microtime(true);
        $autoloads = $autoloadsBuilder->buildAutoloads($composer);
        $elapsed = self::renderElapsedTime($start);
        $io->debug("Generating attributes file: built autoloads in $elapsed");

        $start = microtime(true);
        $classMap = $classMapBuilder->buildClassMap($autoloads);
        $elapsed = self::renderElapsedTime($start);
        $io->debug("Generating attributes file: built class map in $elapsed");

        self::setupAutoload($classMap);

        $start = microtime(true);
        $filter = self::buildFileFilter($composer);
        $classMap = $classMapFilter->filter(
            $classMap,
            fn (string $class, string $filepath): bool => $filter->filter($filepath, $class, $io)
        );
        $elapsed = self::renderElapsedTime($start);
        $io->debug("Generating attributes file: filtered class map in $elapsed");

        $start = microtime(true);
        $collection = $attributeCollector->collectAttributes($classMap);
        $elapsed = self::renderElapsedTime($start);
        $io->debug("Generating attributes file: collected attributes in $elapsed");

        $start = microtime(true);
        $code = self::render($collection);
        $elapsed = self::renderElapsedTime($start);
        $io->debug("Generating attributes file: rendered code in $elapsed");

        file_put_contents($filepath, $code);
    }

    private static function buildDefaultDatastore(): Datastore
    {
        $basePath = Platform::getCwd();

        assert($basePath !== '');

        return new FileDatastore($basePath . DIRECTORY_SEPARATOR . self::CACHE_DIR);
    }

    private static function renderElapsedTime(float $start): string
    {
        return sprintf("%.03f ms", (microtime(true) - $start) * 1000);
    }

    /**
     * @param array<class-string, non-empty-string> $classMap
     */
    private static function setupAutoload(array $classMap): void
    {
        spl_autoload_register(static function (string $class) use ($classMap): void {
            $file = $classMap[$class] ?? null;
            if ($file) {
                require_once $file;
            }
        });
    }

    private static function buildFileFilter(Composer $composer): Filter
    {
        $extra = $composer->getPackage()->getExtra()[self::EXTRA] ?? [];
        /** @var string[] $ignore_paths */
        $ignore_paths = array_merge(
            // @phpstan-ignore-next-line
            $extra[self::EXTRA_IGNORE_PATHS] ?? [],
            self::PROBLEMATIC_PATHS
        );

        return new Filter\Chain([
            new PathFilter($ignore_paths),
            new ContentFilter(),
            new InterfaceFilter()
        ]);
    }

    public static function render(Collector $collector): string
    {
        $targetClassesCode = self::renderTargetClasses($collector->classes);
        $targetMethodsCode = self::renderTargetMethods($collector->methods);
        $targetPropertiesCode = self::renderTargetProperties($collector->properties);

        return <<<PHP
        <?php

        // attributes.php @generated by https://github.com/olvlvl/composer-attribute-collector

        namespace olvlvl\ComposerAttributeCollector;

        Attributes::with(fn () => new Collection(
            targetClasses: [
        $targetClassesCode
            ],
            targetMethods: [
        $targetMethodsCode
            ],
            targetProperties: [
        $targetPropertiesCode
            ],
        ));
        PHP;
    }

    /**
     * @param array<class-string, TargetClassRaw[]> $classes
     */
    private static function renderTargetClasses(array $classes): string
    {
        $code = '';

        foreach ($classes as $attribute => $targets) {
            $code .= "        \\$attribute::class => [\n";
            foreach ($targets as $target) {
                $argumentsCode = self::renderArguments($target->arguments);
                $code .= <<<PHP
                            [ $argumentsCode, \\$target->name::class ],

                PHP;
            }
            $code .= "        ],\n";
        }

        return $code;
    }

    /**
     * @param array<class-string, TargetMethodRaw[]> $methods
     */
    private static function renderTargetMethods(array $methods): string
    {
        $code = '';

        foreach ($methods as $attribute => $targets) {
            $code .= "        \\$attribute::class => [\n";
            foreach ($targets as $target) {
                $argumentsCode = self::renderArguments($target->arguments);
                $code .= <<<PHP
                            [ $argumentsCode, \\$target->class::class, '$target->name' ],

                PHP;
            }
            $code .= "        ],\n";
        }

        return $code;
    }

    /**
     * @param array<class-string, TargetPropertyRaw[]> $properties
     */
    private static function renderTargetProperties(array $properties): string
    {
        $code = '';

        foreach ($properties as $attribute => $targets) {
            $code .= "        \\$attribute::class => [\n";
            foreach ($targets as $target) {
                $argumentsCode = self::renderArguments($target->arguments);
                $code .= <<<PHP
                            [ $argumentsCode, \\$target->class::class, '$target->name' ],

                PHP;
            }
            $code .= "        ],\n";
        }

        return $code;
    }

    /**
     * @param mixed[] $array
     */
    private static function renderArguments(array $array): string
    {
        return var_export($array, true);
    }
}
