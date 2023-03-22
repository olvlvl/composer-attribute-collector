<?php

/*
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace olvlvl\ComposerAttributeCollector;

use Attribute;
use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use olvlvl\ComposerAttributeCollector\Filter\ContentFilter;
use olvlvl\ComposerAttributeCollector\Filter\InterfaceFilter;
use olvlvl\ComposerAttributeCollector\Filter\PathFilter;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

use function array_filter;
use function array_merge;
use function file_put_contents;
use function is_string;
use function microtime;
use function spl_autoload_register;
use function sprintf;
use function var_export;

use const ARRAY_FILTER_USE_BOTH;

/**
 * @internal
 */
final class Plugin implements PluginInterface, EventSubscriberInterface
{
    public const EXTRA = 'composer-attribute-collector';
    public const EXTRA_IGNORE_PATHS = 'ignore-paths';

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

    public static function dump(
        Composer $composer,
        IOInterface $io,
        string $filepath,
        AutoloadsBuilder $autoloadsBuilder = null,
        ClassMapBuilder $classMapBuilder = null
    ): void {
        $autoloadsBuilder ??= new AutoloadsBuilder();
        $classMapBuilder ??= new ClassMapBuilder();

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
        $filter = self::buildClassMapFilter($composer);
        $classMap = array_filter(
            $classMap,
            fn ($class, $filepath) => $filter->filter($class, $filepath, $io),
            ARRAY_FILTER_USE_BOTH
        );
        $elapsed = self::renderElapsedTime($start);
        $io->debug("Generating attributes file: filtered class map in $elapsed");

        $start = microtime(true);
        $collection = self::collectAttributes($classMap, $io);
        $elapsed = self::renderElapsedTime($start);
        $io->debug("Generating attributes file: collected attributes in $elapsed");

        $start = microtime(true);
        $code = self::render($collection);
        $elapsed = self::renderElapsedTime($start);
        $io->debug("Generating attributes file: rendered code in $elapsed");

        file_put_contents($filepath, $code);
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
        spl_autoload_register(function (string $class) use ($classMap) {
            $file = $classMap[$class] ?? null;
            if ($file) {
                require_once $file;
            }
        });
    }

    private static function buildClassMapFilter(Composer $composer): Filter
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

    /**
     * @param array<class-string, non-empty-string> $classMap
     *
     * @throws ReflectionException
     */
    private static function collectAttributes(array $classMap, IOInterface $io): Collector
    {
        $collector = new Collector();

        foreach ($classMap as $class => $filepath) {
            $classReflection = new ReflectionClass($class);

            if (self::isAttribute($classReflection)) {
                continue;
            }

            $attributes = $classReflection->getAttributes();

            foreach ($attributes as $attribute) {
                if (self::isAttributeIgnored($attribute)) {
                    continue;
                }

                $io->debug("Found attribute {$attribute->getName()} on $class");

                $collector->addTargetClass($attribute, $classReflection);
            }

            foreach ($classReflection->getMethods() as $methodReflection) {
                foreach ($methodReflection->getAttributes() as $attribute) {
                    if (self::isAttributeIgnored($attribute)) {
                        continue;
                    }

                    $io->debug("Found attribute {$attribute->getName()} on $class::{$methodReflection->name}");

                    $collector->addTargetMethod($attribute, $methodReflection);
                }
            }
        }

        return $collector;
    }

    public static function render(Collector $collector): string
    {
        $targetClassesCode = self::renderTargetClasses($collector->classes);
        $targetMethodsCode = self::renderTargetMethods($collector->methods);

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
     * @param mixed[] $array
     */
    private static function renderArguments(array $array): string
    {
        return var_export($array, true);
    }

    /**
     * Determines if a class is an attribute.
     *
     * @param ReflectionClass<object> $classReflection
     */
    private static function isAttribute(ReflectionClass $classReflection): bool
    {
        foreach ($classReflection->getAttributes() as $attribute) {
            if ($attribute->getName() === Attribute::class) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ReflectionAttribute<object> $attribute
     */
    private static function isAttributeIgnored(ReflectionAttribute $attribute): bool
    {
        static $ignored = [
            \ReturnTypeWillChange::class => true,
        ];

        return isset($ignored[$attribute->getName()]);
    }
}
