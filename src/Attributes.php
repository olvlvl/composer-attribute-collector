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

use function is_a;

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

    /**
     * @template T of object
     *
     * @param class-string<T> $attribute
     *
     * @return TargetProperty<T>[]
     */
    public static function findTargetProperties(string $attribute): array
    {
        return self::getCollection()->findTargetProperties($attribute);
    }

    /**
     * @param callable(class-string $attribute, class-string $class):bool $predicate
     *
     * @return array<TargetClass<object>>
     */
    public static function filterTargetClasses(callable $predicate): array
    {
        return self::getCollection()->filterTargetClasses($predicate);
    }

    /**
     * @param callable(class-string $attribute, class-string $class, string $method):bool $predicate
     *
     * @return array<TargetMethod<object>>
     */
    public static function filterTargetMethods(callable $predicate): array
    {
        return self::getCollection()->filterTargetMethods($predicate);
    }

    /**
     * @param class-string $class
     *
     * @return Closure(class-string $attribute):bool
     */
    public static function predicateForAttributeInstanceOf(string $class): Closure
    {
        return fn(string $attribute): bool => is_a($attribute, $class, true);
    }

    /**
     * @var array<class-string, ForClass>
     */
    private static array $forClassCache = [];

    /**
     * @param class-string $class
     *
     * @return ForClass
     */
    public static function forClass(string $class): ForClass
    {
        return self::$forClassCache[$class] ??= self::getCollection()->forClass($class);
    }

    private static function getCollection(): Collection
    {
        return self::$collection ??= (
            self::$provider ?? throw new LogicException("provider not set yet")
        )();
    }
}
