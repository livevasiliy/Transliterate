<?php

declare(strict_types=1);

namespace ElForastero\Transliterate;

/**
 * Feel free to change it.
 * Either by pull request or forking.
 *
 * Class Transliteration
 *
 * @author Eugene Dzhumak <elforastero@ya.ru>
 *
 * @version 2.0.0
 */
class Transliteration
{
    /**
     * Transliterate the given string.
     *
     * @param string $string Text to be transliterated
     * @param string $map    Map to be used during transliteration
     *
     * @return string
     */
    public static function make(string $string, string $map = null): string
    {
        $map = self::getMap($map);
        $chars = implode('', array_keys($map));
        $clearedString = preg_replace("/[^\\s\\p{P}\\w${chars}]/iu", '', $string);
        $transliterated = str_replace(array_keys($map), array_values($map), $clearedString);

        return self::applyTransformers($transliterated);
    }

    /**
     * Get map array according to config file.
     *
     * @param string|null $map
     *
     * @return array
     */
    private static function getMap(string $map = null): array
    {
        $map = $map ?? config('transliterate.map');
        $customMaps = config('transliterate.maps');
        $vendorMapsPath = __DIR__.DIRECTORY_SEPARATOR.'maps'.DIRECTORY_SEPARATOR;

        if (null !== $customMaps && array_key_exists($map, $customMaps)) {
            $path = $customMaps[$map];
        } else {
            $path = $vendorMapsPath.$map.'.php';
        }

        if (!file_exists($path)) {
            throw new \InvalidArgumentException("Cant find transliteration map '${map}'");
        }

        return require $path;
    }

    /**
     * Apply a series of transformations defined as closures in the configuration file.
     *
     * @param string $string
     *
     * @return string
     */
    private static function applyTransformers(string $string): string
    {
        foreach (Transformer::getAll() as $transformer) {
            $string = $transformer($string);
        }

        return $string;
    }
}
