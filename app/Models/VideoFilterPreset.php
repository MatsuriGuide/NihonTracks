<?php

namespace App\Models;

use App\Core\Database;

class VideoFilterPreset
{
    public static function allForUser(int $userId): array
    {
        return Database::getInstance()->fetchAll(
            'SELECT * FROM video_filter_presets WHERE user_id = ? ORDER BY name',
            [$userId]
        );
    }

    public static function findById(int $id): ?array
    {
        return Database::getInstance()->fetchOne(
            'SELECT * FROM video_filter_presets WHERE id = ?',
            [$id]
        );
    }

    public static function defaultForUser(int $userId): ?array
    {
        return Database::getInstance()->fetchOne(
            'SELECT * FROM video_filter_presets WHERE user_id = ? AND is_default = 1 LIMIT 1',
            [$userId]
        );
    }

    /**
     * @param int[] $tagIds
     */
    public static function create(
        int $userId,
        string $name,
        ?int $artistId,
        ?string $videoType,
        array $tagIds,
        bool $isDefault
    ): int {
        $db = Database::getInstance();

        if ($isDefault) {
            self::clearDefault($userId);
        }

        $db->query(
            'INSERT INTO video_filter_presets (user_id, name, artist_id, video_type, tag_ids, is_default)
             VALUES (?, ?, ?, ?, ?, ?)',
            [
                $userId,
                $name,
                $artistId,
                $videoType,
                !empty($tagIds) ? json_encode(array_values(array_map('intval', $tagIds))) : null,
                $isDefault ? 1 : 0,
            ]
        );

        return (int) $db->lastInsertId();
    }

    public static function delete(int $id): void
    {
        Database::getInstance()->query('DELETE FROM video_filter_presets WHERE id = ?', [$id]);
    }

    public static function setDefault(int $userId, int $presetId): void
    {
        self::clearDefault($userId);

        Database::getInstance()->query(
            'UPDATE video_filter_presets SET is_default = 1 WHERE id = ? AND user_id = ?',
            [$presetId, $userId]
        );
    }

    public static function clearDefault(int $userId): void
    {
        Database::getInstance()->query(
            'UPDATE video_filter_presets SET is_default = 0 WHERE user_id = ?',
            [$userId]
        );
    }

    /**
     * Active/désactive l'envoi par newsletter pour UN préréglage donné.
     * Contrairement au statut "par défaut", il ne s'agit pas d'un choix
     * exclusif : un utilisateur peut cocher plusieurs filtres à la fois
     * pour sa newsletter (ex. "tout le rock" + "un artiste précis").
     */
    public static function setNewsletterEnabled(int $id, int $userId, bool $enabled): void
    {
        Database::getInstance()->query(
            'UPDATE video_filter_presets SET newsletter_enabled = ? WHERE id = ? AND user_id = ?',
            [$enabled ? 1 : 0, $id, $userId]
        );
    }

    /**
     * Tous les préréglages activés pour la newsletter, groupés par
     * utilisateur — point d'entrée du script d'envoi quotidien.
     *
     * @return array<int, array{email: string, display_name: string, presets: array}>
     */
    public static function allNewsletterSubscribers(): array
    {
        $rows = Database::getInstance()->fetchAll(
            'SELECT p.*, u.email, u.display_name
             FROM video_filter_presets p
             JOIN users u ON u.id = p.user_id
             WHERE p.newsletter_enabled = 1
             ORDER BY p.user_id'
        );

        $byUser = [];
        foreach ($rows as $row) {
            $userId = (int) $row['user_id'];

            if (!isset($byUser[$userId])) {
                $byUser[$userId] = [
                    'email'        => $row['email'],
                    'display_name' => $row['display_name'],
                    'presets'      => [],
                ];
            }

            $byUser[$userId]['presets'][] = $row;
        }

        return $byUser;
    }

    /**
     * @return int[]
     */
    public static function tagIds(array $preset): array
    {
        if (empty($preset['tag_ids'])) {
            return [];
        }

        $decoded = json_decode((string) $preset['tag_ids'], true);

        return is_array($decoded) ? array_map('intval', $decoded) : [];
    }
}
