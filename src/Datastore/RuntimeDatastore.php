<?php

namespace olvlvl\ComposerAttributeCollector\Datastore;

use olvlvl\ComposerAttributeCollector\Datastore;

/**
 * @internal
 */
final class RuntimeDatastore implements Datastore
{
    /**
     * @var array<string, array<int|string, mixed>>
     */
    private array $datastore = [];

    public function get(string $key): array
    {
        return $this->datastore[$key] ?? [];
    }

    public function set(string $key, array $data): void
    {
        $this->datastore[$key] = $data;
    }
}
