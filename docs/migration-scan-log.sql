-- ============================================================
-- NihonTracks - Journal des scans de chaînes YouTube
-- ============================================================

CREATE TABLE scan_log (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    run_at              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    -- cron = déclenché par la tâche planifiée, manual = bouton "Scanner
    -- maintenant" dans /admin/watch, blocked_access = tentative d'accès
    -- direct au script via une requête HTTP (refusée, mais tracée)
    source              ENUM('cron', 'manual', 'blocked_access') NOT NULL,
    channels_scanned    INT UNSIGNED NOT NULL DEFAULT 0,
    suggestions_found   INT UNSIGNED NOT NULL DEFAULT 0,
    errors_count        INT UNSIGNED NOT NULL DEFAULT 0,
    details             TEXT NULL,
    INDEX idx_scan_log_run_at (run_at)
) ENGINE=InnoDB;
