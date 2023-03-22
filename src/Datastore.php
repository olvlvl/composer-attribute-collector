<?php

namespace olvlvl\ComposerAttributeCollector;

interface Datastore
{
    /**
     * @return array<int|string, mixed>
     */
    public function get(string $key): array;

    /**
     * @param array<int|string, mixed> $data
     */
    public function set(string $key, array $data): void;
}
