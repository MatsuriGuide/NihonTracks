<?php

namespace App\Core;

class Slug
{
    public static function make(string $text): string
    {
        $text = mb_strtolower(trim($text));

        $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($transliterated !== false) {
            $text = $transliterated;
        }

        $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';

        return trim($text, '-');
    }
}
