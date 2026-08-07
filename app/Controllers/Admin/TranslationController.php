<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;
use App\Services\OpenAiTranslationService;

class TranslationController extends AdminController
{
    private const LABELS = ['fr' => 'French', 'en' => 'English', 'ja' => 'Japanese'];
    private const VALID_LANGS = ['fr', 'en', 'ja'];

    public function __construct()
    {
        // Traduction réservée aux admins, pas aux modérateurs
        Auth::requireRole('admin');
    }

    public function translateArtist(int $id, string $targetLang): void
    {
        $this->translate('artist', 'artists_i18n', 'artist_id', ['name', 'bio'], $id, $targetLang);
    }

    public function translateVideo(int $id, string $targetLang): void
    {
        $this->translate('video', 'videos_i18n', 'video_id', ['title', 'description'], $id, $targetLang);
    }

    /**
     * @param string[] $fields
     */
    private function translate(string $type, string $table, string $fk, array $fields, int $id, string $targetLang): void
    {
        if (!in_array($targetLang, self::VALID_LANGS, true) || $targetLang === 'fr') {
            $this->redirectBack();

            return;
        }

        $db = Database::getInstance();
        $sourceLang = 'fr';

        $jobId = $this->logJob($type, $id, $sourceLang, $targetLang);

        $source = $db->fetchOne("SELECT * FROM {$table} WHERE {$fk} = ? AND lang = ?", [$id, $sourceLang]);

        if (!$source) {
            $this->markJobFailed($jobId, 'Aucun contenu source (français) à traduire.');
            $this->redirectBack();

            return;
        }

        $translated = [];

        foreach ($fields as $field) {
            $value = $source[$field] ?? null;

            if ($value === null || trim((string) $value) === '') {
                $translated[$field] = null;

                continue;
            }

            $result = OpenAiTranslationService::translate($value, self::LABELS[$targetLang], self::LABELS[$sourceLang]);

            if ($result === null) {
                $this->markJobFailed($jobId, 'Échec de l\'appel à l\'API OpenAI (clé absente/invalide ou erreur réseau).');
                $this->redirectBack();

                return;
            }

            $translated[$field] = $result;
        }

        $existing = $db->fetchOne("SELECT id FROM {$table} WHERE {$fk} = ? AND lang = ?", [$id, $targetLang]);

        if ($existing) {
            $setClause = implode(', ', array_map(static fn (string $f): string => "{$f} = ?", $fields));
            $db->query(
                "UPDATE {$table} SET {$setClause}, is_auto_translated = 1 WHERE {$fk} = ? AND lang = ?",
                [...array_values($translated), $id, $targetLang]
            );
        } else {
            $columns = array_merge([$fk, 'lang'], $fields, ['is_auto_translated']);
            $placeholders = implode(', ', array_fill(0, count($columns), '?'));
            $db->query(
                'INSERT INTO ' . $table . ' (' . implode(', ', $columns) . ") VALUES ({$placeholders})",
                [$id, $targetLang, ...array_values($translated), 1]
            );
        }

        $this->markJobDone($jobId);
        $this->redirectBack();
    }

    private function logJob(string $type, int $id, string $sourceLang, string $targetLang): int
    {
        $db = Database::getInstance();

        $db->query(
            'INSERT INTO translation_jobs (source_type, source_id, source_lang, target_lang, status, triggered_by)
             VALUES (?, ?, ?, ?, "pending", ?)',
            [$type, $id, $sourceLang, $targetLang, (int) Auth::id()]
        );

        return (int) $db->lastInsertId();
    }

    private function markJobDone(int $jobId): void
    {
        Database::getInstance()->query(
            'UPDATE translation_jobs SET status = "done", completed_at = NOW() WHERE id = ?',
            [$jobId]
        );
    }

    private function markJobFailed(int $jobId, string $message): void
    {
        Database::getInstance()->query(
            'UPDATE translation_jobs SET status = "failed", completed_at = NOW(), error_message = ? WHERE id = ?',
            [$message, $jobId]
        );
    }

    private function redirectBack(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/admin';
        $this->redirect($referer);
    }
}
