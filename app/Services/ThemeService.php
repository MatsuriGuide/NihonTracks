<?php

namespace App\Services;

/**
 * Templates (thèmes) de pages d'artistes.
 *
 * Un thème = un dossier public/assets/themes/<slug>/ contenant au minimum theme.css.
 * Un theme.json facultatif donne son nom et sa description : {"name": "Néon", "description": "…"}.
 * Aucun CSS n'est saisi par les utilisateurs : seuls les thèmes présents dans ce dossier existent.
 */
class ThemeService
{
    private static ?array $cache = null;

    private static function baseDir(): string
    {
        return dirname(__DIR__, 2) . '/public/assets/themes';
    }

    /**
     * @return array<string, array{slug: string, name: string, description: string}>
     */
    public static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $themes = [];

        foreach (glob(self::baseDir() . '/*', GLOB_ONLYDIR) ?: [] as $dir) {
            $slug = basename($dir);

            if (preg_match('/^[a-z0-9][a-z0-9-]{0,39}$/', $slug) !== 1 || !is_file($dir . '/theme.css')) {
                continue;
            }

            $meta = [];

            if (is_file($dir . '/theme.json')) {
                $decoded = json_decode((string) file_get_contents($dir . '/theme.json'), true);
                $meta = is_array($decoded) ? $decoded : [];
            }

            $themes[$slug] = [
                'slug'        => $slug,
                'name'        => (string) ($meta['name'] ?? ucfirst($slug)),
                'description' => (string) ($meta['description'] ?? ''),
            ];
        }

        ksort($themes);

        return self::$cache = $themes;
    }

    /**
     * Renvoie le slug s'il correspond à un thème existant, sinon null (thème par défaut).
     */
    public static function valid(?string $slug): ?string
    {
        $slug = $slug === null ? '' : trim($slug);

        return $slug !== '' && isset(self::all()[$slug]) ? $slug : null;
    }

    /**
     * Couleur d'accent « #rrggbb » valide (minuscules), sinon null.
     */
    public static function accent(?string $color): ?string
    {
        $color = $color === null ? '' : strtolower(trim($color));

        return preg_match('/^#[0-9a-f]{6}$/', $color) === 1 ? $color : null;
    }
}
