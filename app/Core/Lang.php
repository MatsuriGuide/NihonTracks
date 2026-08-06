<?php

namespace App\Core;

class Lang
{
    private static array $siteLangs = ['fr', 'en', 'ja'];
    private static string $defaultLang = 'fr';
    private static ?string $currentLang = null;

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
