<?php

namespace olvlvl\ComposerAttributeCollector;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function serialize;
use function unserialize;

use const DIRECTORY_SEPARATOR;

final class FileDatastore implements Datastore
{
    public function __construct(
        private string $dir
    ) {
        if (!is_dir($dir)) {
            mkdir($dir);
        }
    }

    public function get(string $key): array
    {
        $filename = $this->dir . DIRECTORY_SEPARATOR . $key;

        if (!file_exists($filename)) {
            return [];
        }

        /** @phpstan-ignore-next-line */
        return unserialize(file_get_contents($filename));
    }

    public function set(string $key, array $data): void
    {
        $filename = $this->dir . DIRECTORY_SEPARATOR . $key;

        file_put_contents($filename, serialize($data));
    }
}
