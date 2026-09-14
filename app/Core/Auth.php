<?php

namespace App\Core;

class Auth
{
    public static function check(): bool
    {
        return !empty($_SESSION['user']);
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function id(): ?int
    {
        return $_SESSION['user']['id'] ?? null;
    }

    public static function role(): ?string
    {
        return $_SESSION['user']['role'] ?? null;
    }

    public static function login(array $userRow): void
    {
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id'   => (int) $userRow['id'],
            'role' => $userRow['role'],
            'lang' => $userRow['preferred_lang'] ?? 'fr',
            'name' => $userRow['display_name'],
        ];
    }

    public static function logout(): void
    {
        unset($_SESSION['user']);
        session_regenerate_id(true);
    }

    /**
     * Bloque l'accès si l'utilisateur n'a pas un des rôles autorisés.
     * Distingue deux cas bien différents : pas connecté du tout (session
     * expirée, jamais connecté...) → redirection vers la connexion, comme
     * requireLogin(). Connecté mais rôle insuffisant → 403, le vrai cas
     * de droits refusés.
     *
     * @param string|string[] $roles
     */
    public static function requireRole(string|array $roles): void
    {
        if (!self::check()) {
            header('Location: /login');
            exit;
        }

        $roles = (array) $roles;

        if (!in_array(self::role(), $roles, true)) {
            http_response_code(403);
            require dirname(__DIR__) . '/Views/errors/403.php';
            exit;
        }
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: /login');
            exit;
        }
    }

    /**
     * Un utilisateur peut modifier ce contenu s'il en est l'auteur, ou s'il est admin.
     *
     * Le modérateur n'a PAS ce droit hors résolution d'un signalement :
     * cf. Services/ModerationService::resolve(), seul point d'entrée autorisant
     * une modification cross-utilisateur par un modérateur.
     */
    public static function canEdit(int $ownerId): bool
    {
        if (!self::check()) {
            return false;
        }

        return self::id() === $ownerId || self::role() === 'admin';
    }
}
