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
final class TargetMethodRaw
{
    // @phpstan-ignore-next-line
    public static function __set_state(array $args): object
    {
        return new self($args['arguments'], $args['class'], $args['name']);
    }

    /**
     * @param array<string, mixed> $arguments
     * @param class-string $class
     * @param string $name
     */
    public function __construct(
        public array $arguments,
        public string $class,
        public string $name
    ) {
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $attribute
     *
     * @return TargetMethod<T>
     */
    public function toTarget(string $attribute): TargetMethod
    {
        return new TargetMethod(new $attribute(...$this->arguments), $this->class, $this->name);
    }
}
