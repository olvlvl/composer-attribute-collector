<?php

namespace olvlvl\ComposerAttributeCollector;

use Composer\IO\IOInterface;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_array;
use function is_dir;
use function mkdir;
use function restore_error_handler;
use function serialize;
use function set_error_handler;
use function unserialize;

use const DIRECTORY_SEPARATOR;

/**
 * @internal
 */
final class FileDatastore implements Datastore
{
    /**
     * @param non-empty-string $dir
     */
    public function __construct(
        private string $dir,
        private IOInterface $io,
    ) {
        assert($dir !== '');

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

        return self::safeGet($filename);
    }

    public function set(string $key, array $data): void
    {
        $filename = $this->dir . DIRECTORY_SEPARATOR . $key;

        file_put_contents($filename, serialize($data));
    }

    /**
     * @return mixed[]
     */
    private function safeGet(string $filename): array
    {
        $str = file_get_contents($filename);

        if ($str === false) {
            return [];
        }

        $errored = false;

        set_error_handler(function (int $errno, string $errstr) use (&$errored, $filename): bool {
            $errored = true;

            $this->io->warning("Unable to unserialize cache item $filename: $errstr");

            return true;
        });

        $ar = unserialize($str);

        restore_error_handler();

        if ($errored || !is_array($ar)) {
            return [];
        }

        return $ar;
    }
}
