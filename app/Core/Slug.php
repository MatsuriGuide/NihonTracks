<?php

namespace App\Core;

class Slug
{
    public static function make(string $text): string
    {
        $text = mb_strtolower(trim($text));

        // Remplace tout ce qui n'est pas une lettre/chiffre Unicode par un
        // tiret, SANS translittération forcée vers l'ASCII : les caractères
        // japonais/coréens/etc. sont préservés tels quels. Essentiel pour ce
        // site, où beaucoup de noms d'artistes n'ont pas d'équivalent latin
        // (une translittération ASCII produirait un slug vide, cassant l'URL).
        $text = preg_replace('/[^\p{L}\p{N}]+/u', '-', $text) ?? '';
        $text = trim($text, '-');

        if ($text !== '') {
            return $text;
        }

        // Repli pour le cas extrêmement rare d'un nom sans aucune lettre ni
        // chiffre exploitable (emojis seuls, symboles...).
        return 'artist-' . substr(bin2hex(random_bytes(4)), 0, 8);
    }
}
