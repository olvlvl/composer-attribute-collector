<?php

namespace olvlvl\ComposerAttributeCollector;

use function var_export;

/**
 * Renders collected attribute targets as PHP code.
 *
 * @internal
 */
final class TransientCollectionRenderer
{
    public static function render(TransientCollection $collector): string
    {
        $targetClassesCode = self::targetsToCode($collector->classes);
        $targetMethodsCode = self::targetsToCode($collector->methods);
        $targetPropertiesCode = self::targetsToCode($collector->properties);

        return <<<PHP
        <?php

        // attributes.php @generated by https://github.com/olvlvl/composer-attribute-collector

        namespace olvlvl\ComposerAttributeCollector;

        Attributes::with(fn () => new Collection(
            targetClasses: $targetClassesCode,
            targetMethods: $targetMethodsCode,
            targetProperties: $targetPropertiesCode,
        ));
        PHP;
    }

    /**
     * //phpcs:disable Generic.Files.LineLength.TooLong
     * @param iterable<class-string, iterable<TransientTargetClass|TransientTargetMethod|TransientTargetProperty>> $targetByClass
     *
     * @return string
     */
    private static function targetsToCode(iterable $targetByClass): string
    {
        $array = self::targetsToArray($targetByClass);

        return var_export($array, true);
    }

    /**
     * //phpcs:disable Generic.Files.LineLength.TooLong
     * @param iterable<class-string, iterable<TransientTargetClass|TransientTargetMethod|TransientTargetProperty>> $targetByClass
     *
     * @return array<class-string, array<array{ array<int|string, mixed>, class-string, 2?:non-empty-string }>>
     */
    private static function targetsToArray(iterable $targetByClass): array
    {
        $by = [];

        foreach ($targetByClass as $class => $targets) {
            foreach ($targets as $t) {
                $a = [ $t->arguments, $class ];

                if ($t instanceof TransientTargetMethod || $t instanceof TransientTargetProperty) {
                    $a[] = $t->name;
                }

                $by[$t->attribute][] = $a;
            }
        }

        return $by;
    }
}