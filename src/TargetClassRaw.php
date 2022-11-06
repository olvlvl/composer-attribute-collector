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
    // @phpstan-ignore-next-line
    public static function __set_state(array $args): object
    {
        return new self($args['arguments'], $args['name']);
    }

    /**
     * @param array<int|string, mixed> $arguments
     * @param class-string $name
     */
    public function __construct(
        public array $arguments,
        public string $name
    ) {
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $attribute
     *
     * @return TargetClass<T>
     */
    public function toTarget(string $attribute): TargetClass
    {
        return new TargetClass(new $attribute(...$this->arguments), $this->name);
    }
}
