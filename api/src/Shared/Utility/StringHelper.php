<?php

declare(strict_types=1);

namespace App\Shared\Utility;

class StringHelper
{
    public static function slugify($text): string
    {
        // Replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', (string) $text);

        // Transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', (string) $text);

        // Remove unwanted characters
        $text = preg_replace('~[^\-\w]+~', '', $text);

        // Trim
        $text = trim((string) $text, '-');

        // Remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // Lowercase
        $text = strtolower((string) $text);

        if ($text === '' || $text === '0') {
            return 'n-a';
        }

        return $text;
    }
}
