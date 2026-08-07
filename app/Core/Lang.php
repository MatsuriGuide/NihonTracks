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

        // 3. Repli sur la langue par défaut du site
        return self::$defaultLang;
    }
}
