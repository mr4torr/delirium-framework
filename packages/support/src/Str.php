<?php

declare(strict_types=1);

namespace Delirium\Support;

class Str
{
    /**
     * Generate a more truly "random" alpha-numeric string.
     *
     * @param int $length
     * @return string
     */
    public static function random(int $length = 16): string
    {
        $string = '';

        while (($len = strlen($string)) < $length) {
            $size = $length - $len;

            $bytes = random_bytes($size);

            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }

    /**
     * Determine if a given string contains a given substring.
     *
     * @param string $haystack
     * @param string|iterable<string> $needles
     * @return bool
     */
    public static function contains(string $haystack, string|iterable $needles): bool
    {
        if (is_string($needles)) {
            return str_contains($haystack, $needles);
        }

        foreach ($needles as $needle) {
            if (str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }
}
