<?php

namespace olvlvl\ComposerAttributeCollector;

/**
 * @internal
 *
 * //phpcs:disable Generic.Files.LineLength.TooLong
 */
interface Collection
{
    /**
     * @template T of object
     *
     * @param class-string<T> $attribute
     *
     * @return array<TargetClass<T>>
     */
    public function findTargetClasses(string $attribute): array;

    /**
     * @template T of object
     *
     * @param class-string<T> $attribute
     *
     * @return array<TargetMethod<T>>
     */
    public function findTargetMethods(string $attribute): array;

    /**
     * @template T of object
     *
     * @param class-string<T> $attribute
     *
     * @return array<TargetParameter<T>>
     */
    public function findTargetParameters(string $attribute): array;

    /**
     * @template T of object
     *
     * @param class-string<T> $attribute
     *
     * @return array<TargetProperty<T>>
     */
    public function findTargetProperties(string $attribute): array;

    /**
     * @param callable(class-string $attribute, class-string $class):bool $predicate
     *
     * @return array<TargetClass<object>>
     */
    public function filterTargetClasses(callable $predicate): array;

    /**
     * @param callable(class-string $attribute, class-string $class, non-empty-string $method):bool $predicate
     *
     * @return array<TargetMethod<object>>
     */
    public function filterTargetMethods(callable $predicate): array;

    /**
     * @param callable(class-string $attribute, class-string $class, non-empty-string $method, non-empty-string $parameter):bool $predicate
     *
     * @return array<TargetParameter<object>>
     */
    public function filterTargetParameters(callable $predicate): array;

    /**
     * @param callable(class-string $attribute, class-string $class, non-empty-string $property):bool $predicate
     *
     * @return array<TargetProperty<object>>
     */
    public function filterTargetProperties(callable $predicate): array;
}
