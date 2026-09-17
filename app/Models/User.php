<?php

namespace App\Models;

use App\Core\Database;

class User
{
    public static function findByEmail(string $email): ?array
    {
        return Database::getInstance()->fetchOne(
            'SELECT * FROM users WHERE email = ?',
            [$email]
        );
    }

    public static function findById(int $id): ?array
    {
        return Database::getInstance()->fetchOne(
            'SELECT * FROM users WHERE id = ?',
            [$id]
        );
    }

    public static function emailExists(string $email): bool
    {
        return self::findByEmail($email) !== null;
    }

    public static function create(string $email, string $password, string $displayName): int
    {
        $db = Database::getInstance();

        $db->query(
            'INSERT INTO users (email, password_hash, display_name) VALUES (?, ?, ?)',
            [$email, password_hash($password, PASSWORD_BCRYPT), $displayName]
        );

        return (int) $db->lastInsertId();
    }

    public static function findByUnsubscribeToken(string $token): ?array
    {
        return Database::getInstance()->fetchOne(
            'SELECT id, display_name FROM users WHERE newsletter_unsubscribe_token = ?',
            [$token]
        );
    }

    /**
     * Génère et enregistre un jeton de désabonnement stable pour cet
     * utilisateur s'il n'en a pas déjà un — pas besoin de le préremplir
     * pour tout le monde à l'avance, il naît au premier envoi de newsletter.
     */
    public static function getOrCreateUnsubscribeToken(int $userId): string
    {
        $db = Database::getInstance();

        $existing = $db->fetchOne(
            'SELECT newsletter_unsubscribe_token FROM users WHERE id = ?',
            [$userId]
        );

        if (!empty($existing['newsletter_unsubscribe_token'])) {
            return $existing['newsletter_unsubscribe_token'];
        }

        $token = bin2hex(random_bytes(24));

        $db->query(
            'UPDATE users SET newsletter_unsubscribe_token = ? WHERE id = ?',
            [$token, $userId]
        );

        return $token;
    }
}
