<?php

namespace olvlvl\ComposerAttributeCollector;

/**
 * @internal
 */
interface Datastore
{
    /**
     * @return array<class-string, array{
     *     int,
     *     array<TransientTargetClass>,
     *     array<TransientTargetMethod>,
     *     array<TransientTargetProperty>,
     * }>
     *     Where _key_ is a class and _value is an array where:
     *     - `0` is a timestamp
     *     - `1` is an array of class attributes
     *     - `2` is an array of method attributes
     *     - `3` is an array of property attributes
     */
    public function get(string $key): array;

    /**
     * @param array<class-string, array{
     *     int,
     *     array<TransientTargetClass>,
     *     array<TransientTargetMethod>,
     *     array<TransientTargetProperty>,
     * }> $data
     */
    public function set(string $key, array $data): void;
}
