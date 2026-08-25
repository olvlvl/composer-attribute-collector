<?php

namespace olvlvl\ComposerAttributeCollector;

use olvlvl\ComposerAttributeCollector\Factory\CollectionRendererFactory;
use olvlvl\ComposerAttributeCollector\Factory\DatastoreFactory;
use olvlvl\ComposerAttributeCollector\Factory\FilterFactory;

use function file_put_contents;
use function microtime;

/**
 * Orchestrates the collection of the attributes and the generation of the 'attributes.php' file.
 *
 * @internal
 */
final readonly class Collector
{
    public function __construct(
        private Config $config,
        private Logger $log,
    ) {
    }

    /**
     * Orchestrates the collection of the attributes and the generation of the 'attributes.php' file.
     */
    public function run(): void
    {
        // setup
        $datastore = DatastoreFactory::from($this->config, $this->log);
        $filter = FilterFactory::create();
        $renderer = CollectionRendererFactory::from($this->config);

        // steps
        $classMap = $this->generateClassMap($datastore);
        $filteredClassMap = $this->filterClassMap($classMap, $filter, $datastore);
        $collection = $this->collectAttributes($filteredClassMap, $datastore);

        // render
        $this->render($collection, $renderer);
    }

    /**
     * @return array<class-string, non-empty-string>
     *     Where _key_ is a class and _value_ its path.
     */
    private function generateClassMap(Datastore $datastore): array
    {
        $start = microtime(true);

        $classMapGenerator = new MemoizeClassMapGenerator($datastore, $this->log);

        foreach ($this->config->include as $include) {
            $classMapGenerator->scanPaths($include, $this->config->excludeRegExp);
        }

        $this->logElapsed('scanning paths', $start);

        return $classMapGenerator->getMap();
    }

    /**
     * @param array<class-string, non-empty-string> $classMap
     *     Where _key_ is a class and _value_ its pathname.
     *
     * @return array<class-string, non-empty-string>
     */
    private function filterClassMap(array $classMap, Filter $filter, Datastore $datastore): array
    {
        $start = microtime(true);

        $classMapFilter = new MemoizeClassMapFilter($datastore, $this->log);

        $filtered = $classMapFilter->filter(
            $classMap,
            fn (string $class, string $filepath): bool => $filter->filter($filepath, $class, $this->log),
        );

        $this->logElapsed('filtering class map', $start);

        return $filtered;
    }

    /**
     * @param array<class-string, non-empty-string> $classMap
     *     Where _key_ is a class and _value_ its pathname.
     */
    private function collectAttributes(array $classMap, Datastore $datastore): TransientCollection
    {
        $start = microtime(true);

        $attributeCollector = new MemoizeAttributeCollector(
            new ClassAttributeCollector($this->log),
            $datastore,
            $this->log,
        );

        $collection = $attributeCollector->collectAttributes($classMap);

        $this->logElapsed('collecting attributes', $start);

        return $collection;
    }

    /**
     * @param class-string<CollectionRenderer> $renderer
     */
    private function render(TransientCollection $collection, string $renderer): void
    {
        $start = microtime(true);

        $code = $renderer::render($collection);

        file_put_contents($this->config->attributesFile, $code);

        $this->logElapsed('rendering code', $start);
    }

    private function logElapsed(string $label, float $start): void
    {
        $this->log->debug("Generating attributes file: $label in " . ElapsedTime::render($start));
    }
}
