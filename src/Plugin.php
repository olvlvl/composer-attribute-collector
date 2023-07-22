<?php

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

use function file_put_contents;
use function microtime;
use function spl_autoload_register;
use function sprintf;

use const DIRECTORY_SEPARATOR;

/**
 * @internal
 */
final class Plugin implements PluginInterface, EventSubscriberInterface
{
    public const CACHE_DIR = '.composer-attribute-collector';

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
        $config = Config::from($composer);
        $io = $event->getIO();

        $start = microtime(true);
        $io->write('<info>Generating attributes file</info>');
        self::dump($event->getComposer(), $config, $io);
        $elapsed = self::renderElapsedTime($start);
        $io->write("<info>Generated attributes file in $elapsed</info>");
    }

    /**
     * @throws ReflectionException
     */
    public static function dump(
        Composer $composer,
        Config $config,
        IOInterface $io,
        AutoloadsBuilder $autoloadsBuilder = null,
        ClassMapBuilder $classMapBuilder = null
    ): void {
        $datastore = self::buildDefaultDatastore($io);
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
        $filter = self::buildFileFilter($config);
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

        file_put_contents($config->attributesFile, $code);
    }

    private static function buildDefaultDatastore(IOInterface $io): Datastore
    {
        $basePath = Platform::getCwd();

        assert($basePath !== '');

        return new FileDatastore($basePath . DIRECTORY_SEPARATOR . self::CACHE_DIR, $io);
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

    private static function buildFileFilter(Config $config): Filter
    {
        return new Filter\Chain([
            new PathFilter(
                include: $config->include,
                exclude: $config->exclude
            ),
            new ContentFilter(),
            new InterfaceFilter()
        ]);
    }

    private static function render(Collector $collector): string
    {
        return CollectionRenderer::render($collector);
    }
}
