<?php

namespace olvlvl\ComposerAttributeCollector;

use olvlvl\ComposerAttributeCollector\Datastore\FileDatastore;
use olvlvl\ComposerAttributeCollector\Datastore\RuntimeDatastore;
use olvlvl\ComposerAttributeCollector\Filter\ClassFilter;
use olvlvl\ComposerAttributeCollector\Filter\ContentFilter;
use RuntimeException;

/**
 * @internal
 * @readonly
 */
final class Collector
{
    public function __construct(
        private Config $config,
        private Logger $log,
    ) {
    }

    /**
     * Dumps the 'attributes.php' file.
     */
    public function dump(): void
    {
        $config = $this->config;
        $log =  $this->log;

        //
        // Scan the included paths
        //
        $start = microtime(true);
        $datastore = $this->buildDefaultDatastore();
        $classMapGenerator = new MemoizeClassMapGenerator($datastore, $log);
        foreach ($config->include as $include) {
            $classMapGenerator->scanPaths($include, $config->excludeRegExp);
        }
        $classMap = $classMapGenerator->getMap();
        $elapsed = ElapsedTime::render($start);
        $log->debug("Generating attributes file: scanned paths in $elapsed");

        //
        // Filter the class map
        //
        $start = microtime(true);
        $classMapFilter = new MemoizeClassMapFilter($datastore, $log);
        $filter = $this->buildFileFilter();
        $classMap = $classMapFilter->filter(
            $classMap,
            fn (string $class, string $filepath): bool => $filter->filter($filepath, $class, $log)
        );
        $elapsed = ElapsedTime::render($start);
        $log->debug("Generating attributes file: filtered class map in $elapsed");

        //
        // Collect attributes
        //
        $start = microtime(true);
        $attributeCollector = new MemoizeAttributeCollector(new ClassAttributeCollector($log), $datastore, $log);
        $collection = $attributeCollector->collectAttributes($classMap);
        $elapsed = ElapsedTime::render($start);
        $log->debug("Generating attributes file: collected attributes in $elapsed");

        //
        // Render attributes
        //
        $start = microtime(true);
        $code = $this->render($collection);
        file_put_contents($config->attributesFile, $code);
        $elapsed = ElapsedTime::render($start);
        $log->debug("Generating attributes file: rendered code in $elapsed");
    }

    private function buildDefaultDatastore(): Datastore
    {
        if (!$this->config->useCache) {
            return new RuntimeDatastore();
        }

        $basePath = getcwd() ?: throw new RuntimeException('Unable to locate base path');

        return new FileDatastore($basePath . DIRECTORY_SEPARATOR . Plugin::CACHE_DIR, $this->log);
    }

    private function buildFileFilter(): Filter
    {
        return new Filter\Chain([
            new ContentFilter(),
            new ClassFilter()
        ]);
    }

    private function render(TransientCollection $collector): string
    {
        return TransientCollectionRenderer::render($collector);
    }
}
