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
 *
 * @template T of object
 */
final class TargetClass
{
    /**
     * @param T $attribute
     * @param class-string $name
     */
    public function __construct(
        public object $attribute,
        public string $name,
    ) {
    }
}
