-- ============================================================
-- NihonTracks - Surveillance de chaînes YouTube
-- Table des suggestions de vidéos détectées automatiquement
-- ============================================================

CREATE TABLE video_suggestions (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    artist_id       INT UNSIGNED NOT NULL,
    youtube_id      VARCHAR(20) NOT NULL UNIQUE,
    title           VARCHAR(255) NULL,
    thumbnail_url   VARCHAR(500) NULL,
    channel_name    VARCHAR(150) NULL,
    published_at    DATE NULL,
    -- pending = à valider, dismissed = rejetée (ne sera plus jamais reproposée
    -- puisque youtube_id reste connu en base), imported = convertie en vidéo réelle
    status          ENUM('pending', 'dismissed', 'imported') NOT NULL DEFAULT 'pending',
    discovered_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reviewed_at     DATETIME NULL,
    reviewed_by     INT UNSIGNED NULL,
    FOREIGN KEY (artist_id) REFERENCES artists(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_suggestions_status (status)
) ENGINE=InnoDB;
