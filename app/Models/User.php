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
}
