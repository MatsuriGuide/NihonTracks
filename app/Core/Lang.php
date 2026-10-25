<?php

namespace App\Core;

class Lang
{
    private static array $siteLangs = ['fr', 'en', 'ja'];
    private static string $defaultLang = 'fr';
    private static ?string $currentLang = null;
    private static array $dictionaries = [];

    public static function getSiteLangs(): array
    {
        return self::$siteLangs;
    }

    public static function getDefaultLang(): string
    {
        return self::$defaultLang;
    }

    public static function current(): string
    {
        if (self::$currentLang === null) {
            self::$currentLang = self::detect();
        }

        return self::$currentLang;
    }

    public static function set(string $lang): void
    {
        if (in_array($lang, self::$siteLangs, true)) {
            $_SESSION['lang'] = $lang;
            self::$currentLang = $lang;
            self::rememberChoice($lang);
        }
    }

    /**
     * Traduit une clé d'interface (menus, boutons, labels...) dans la langue
     * courante, avec repli sur la langue par défaut puis sur la clé elle-même
     * si aucune traduction n'existe.
     */
    public static function t(string $key, ?string $default = null): string
    {
        $dict = self::dictionary(self::current());

        if (isset($dict[$key])) {
            return $dict[$key];
        }

        $fallback = self::dictionary(self::$defaultLang);

        return $fallback[$key] ?? ($default ?? $key);
    }

    private static function dictionary(string $lang): array
    {
        if (!isset(self::$dictionaries[$lang])) {
            $file = dirname(__DIR__) . "/Lang/{$lang}.php";
            self::$dictionaries[$lang] = file_exists($file) ? require $file : [];
        }

        return self::$dictionaries[$lang];
    }

    private const COOKIE_NAME = 'nt_lang';
    private const COOKIE_LIFETIME = 31536000; // 1 an

    /**
     * Mémorise le choix explicite dans un cookie, pour que le visiteur
     * retrouve sa langue à sa prochaine visite (la session, elle, expire).
     */
    private static function rememberChoice(string $lang): void
    {
        if (PHP_SAPI === 'cli' || headers_sent()) {
            return;
        }

        setcookie(self::COOKIE_NAME, $lang, [
            'expires'  => time() + self::COOKIE_LIFETIME,
            'path'     => '/',
            'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    /**
     * Langue préférée déclarée par le navigateur (en-tête Accept-Language,
     * ex. "ja,en-US;q=0.9,fr;q=0.8"), restreinte aux langues du site.
     * Une langue non gérée (ex. "de") est ignorée au profit de la suivante.
     */
    public static function fromAcceptLanguage(?string $header): ?string
    {
        if ($header === null || trim($header) === '') {
            return null;
        }

        $candidates = [];

        foreach (explode(',', $header) as $position => $part) {
            $pieces = explode(';', trim($part));
            $tag = strtolower(trim($pieces[0]));
            $quality = 1.0;

            foreach (array_slice($pieces, 1) as $param) {
                $param = trim($param);
                if (strncasecmp($param, 'q=', 2) === 0) {
                    $quality = (float) substr($param, 2);
                }
            }

            if ($tag === '' || $tag === '*' || $quality <= 0) {
                continue;
            }

            // "ja-JP" -> "ja", "en-US" -> "en"
            $candidates[] = [substr($tag, 0, 2), $quality, $position];
        }

        // Tri par qualité décroissante, puis par ordre d'apparition.
        usort($candidates, static function (array $a, array $b): int {
            return [$b[1], $a[2]] <=> [$a[1], $b[2]];
        });

        foreach ($candidates as $candidate) {
            if (in_array($candidate[0], self::$siteLangs, true)) {
                return $candidate[0];
            }
        }

        return null;
    }

    private static function detect(): string
    {
        // 1. Choix explicite en session
        if (!empty($_SESSION['lang']) && in_array($_SESSION['lang'], self::$siteLangs, true)) {
            return $_SESSION['lang'];
        }

        // 2. Préférence de l'utilisateur connecté
        if (!empty($_SESSION['user']['lang']) && in_array($_SESSION['user']['lang'], self::$siteLangs, true)) {
            return $_SESSION['user']['lang'];
        }

        // 3. Choix mémorisé lors d'une visite précédente (cookie)
        $cookie = $_COOKIE[self::COOKIE_NAME] ?? '';
        if (in_array($cookie, self::$siteLangs, true)) {
            return $cookie;
        }

        // 4. Langue du navigateur
        $fromBrowser = self::fromAcceptLanguage($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null);
        if ($fromBrowser !== null) {
            return $fromBrowser;
        }

        // 5. Repli sur la langue par défaut du site
        return self::$defaultLang;
    }
}
