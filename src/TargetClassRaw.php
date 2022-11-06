<?php

/*
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace olvlvl\ComposerAttributeCollector;

/**
 * @readonly
 * @internal
 */
final class TargetClassRaw
{
    /**
     * @param array<int|string, mixed> $arguments
     * @param class-string $name
     */
    public function __construct(
        public array $arguments,
        public string $name
    ) {
    }
}
