<?php

/*
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace olvlvl\ComposerAttributeCollector;

use Closure;
use LogicException;

final class Attributes
{
    /**
     * @var Closure():Collection|null
     */
    private static ?Closure $provider = null;
    private static ?Collection $collection;

    public static function with(Closure $provider): ?Closure
    {
        $previous = self::$provider;

        self::$collection = null;
        self::$provider = $provider;

        return $previous;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $attribute
     *
     * @return TargetClass<T>[]
     */
    public static function findTargetClasses(string $attribute): array
    {
        return self::getCollection()->findTargetClasses($attribute);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $attribute
     *
     * @return TargetMethod<T>[]
     */
    public static function findTargetMethods(string $attribute): array
    {
        return self::getCollection()->findTargetMethods($attribute);
    }

    private static function getCollection(): Collection
    {
        return self::$collection ??= (
            self::$provider ?? throw new LogicException("provider not set yet")
        )();
    }
}
