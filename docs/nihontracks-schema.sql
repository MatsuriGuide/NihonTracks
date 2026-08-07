-- ============================================================
-- NihonTracks - Schéma de base de données complet
-- Base cible : u320069492_NihonTracks (déjà créée sur l'hébergement)
-- MySQL 8+ / InnoDB / utf8mb4
-- ============================================================

-- ============================================================
-- UTILISATEURS
-- ============================================================

CREATE TABLE users (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email           VARCHAR(255) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    display_name    VARCHAR(100) NOT NULL,
    role            ENUM('user', 'moderator', 'admin') NOT NULL DEFAULT 'user',
    preferred_lang  ENUM('fr', 'en', 'ja') NOT NULL DEFAULT 'fr',
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- ARTISTES
-- ============================================================

CREATE TABLE artists (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type            ENUM('solo', 'group', 'duo', 'other') NOT NULL DEFAULT 'solo',
    status          ENUM('active', 'disbanded', 'hiatus') NOT NULL DEFAULT 'active',
    start_year      SMALLINT UNSIGNED NULL,
    end_year        SMALLINT UNSIGNED NULL,
    label           VARCHAR(150) NULL,
    avatar_path     VARCHAR(255) NULL,
    slug            VARCHAR(150) NOT NULL UNIQUE,
    created_by      INT UNSIGNED NOT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_artists_status (status),
    INDEX idx_artists_type (type)
) ENGINE=InnoDB;

CREATE TABLE artists_i18n (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    artist_id       INT UNSIGNED NOT NULL,
    lang            ENUM('fr', 'en', 'ja') NOT NULL,
    name            VARCHAR(150) NOT NULL,
    bio             TEXT NULL,
    is_auto_translated TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (artist_id) REFERENCES artists(id) ON DELETE CASCADE,
    UNIQUE KEY uq_artist_lang (artist_id, lang)
) ENGINE=InnoDB;

CREATE TABLE artist_links (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    artist_id       INT UNSIGNED NOT NULL,
    platform        ENUM('website', 'twitter', 'instagram', 'tiktok', 'youtube', 'spotify', 'other') NOT NULL,
    url             VARCHAR(500) NOT NULL,
    FOREIGN KEY (artist_id) REFERENCES artists(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE artist_relations (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    artist_id           INT UNSIGNED NOT NULL,
    related_artist_id   INT UNSIGNED NOT NULL,
    relation_type       ENUM('member_of', 'former_member_of', 'solo_project_of', 'collaborates_with') NOT NULL,
    note                VARCHAR(255) NULL,
    FOREIGN KEY (artist_id) REFERENCES artists(id) ON DELETE CASCADE,
    FOREIGN KEY (related_artist_id) REFERENCES artists(id) ON DELETE CASCADE,
    UNIQUE KEY uq_relation (artist_id, related_artist_id, relation_type)
) ENGINE=InnoDB;

-- ============================================================
-- TAGS
-- ============================================================

CREATE TABLE tag_categories (
    id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug    VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE tag_categories_i18n (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id     INT UNSIGNED NOT NULL,
    lang            ENUM('fr', 'en', 'ja') NOT NULL,
    name            VARCHAR(100) NOT NULL,
    FOREIGN KEY (category_id) REFERENCES tag_categories(id) ON DELETE CASCADE,
    UNIQUE KEY uq_category_lang (category_id, lang)
) ENGINE=InnoDB;

CREATE TABLE tags (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id     INT UNSIGNED NULL,
    slug            VARCHAR(100) NOT NULL UNIQUE,
    created_by      INT UNSIGNED NULL,        -- NULL = tag "système" (seed initial)
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES tag_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE tags_i18n (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tag_id          INT UNSIGNED NOT NULL,
    lang            ENUM('fr', 'en', 'ja') NOT NULL,
    name            VARCHAR(100) NOT NULL,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE,
    UNIQUE KEY uq_tag_lang (tag_id, lang)
) ENGINE=InnoDB;

CREATE TABLE artist_tags (
    artist_id   INT UNSIGNED NOT NULL,
    tag_id      INT UNSIGNED NOT NULL,
    PRIMARY KEY (artist_id, tag_id),
    FOREIGN KEY (artist_id) REFERENCES artists(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- VIDEOS
-- ============================================================

CREATE TABLE videos (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    youtube_id      VARCHAR(20) NOT NULL UNIQUE,
    youtube_url     VARCHAR(500) NOT NULL,
    release_date    DATE NULL,
    video_type      ENUM('mv', 'lyric_video', 'live', 'performance', 'cover', 'teaser', 'other') NOT NULL DEFAULT 'mv',
    thumbnail_url   VARCHAR(500) NULL,
    channel_name    VARCHAR(150) NULL,
    duration_seconds INT UNSIGNED NULL,
    added_by        INT UNSIGNED NOT NULL,
    status          ENUM('published', 'hidden') NOT NULL DEFAULT 'published',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (added_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_videos_release_date (release_date),
    INDEX idx_videos_status (status)
) ENGINE=InnoDB;

CREATE TABLE videos_i18n (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    video_id        INT UNSIGNED NOT NULL,
    lang            ENUM('fr', 'en', 'ja') NOT NULL,
    title           VARCHAR(255) NOT NULL,
    description     TEXT NULL,
    is_auto_translated TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE,
    UNIQUE KEY uq_video_lang (video_id, lang)
) ENGINE=InnoDB;

CREATE TABLE video_artists (
    video_id    INT UNSIGNED NOT NULL,
    artist_id   INT UNSIGNED NOT NULL,
    role        ENUM('main', 'feat', 'cover_of') NOT NULL DEFAULT 'main',
    PRIMARY KEY (video_id, artist_id, role),
    FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE,
    FOREIGN KEY (artist_id) REFERENCES artists(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE video_tags (
    video_id    INT UNSIGNED NOT NULL,
    tag_id      INT UNSIGNED NOT NULL,
    PRIMARY KEY (video_id, tag_id),
    FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- PLAYLISTS
-- ============================================================

CREATE TABLE playlists (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    name            VARCHAR(150) NOT NULL,
    description     TEXT NULL,
    is_public       TINYINT(1) NOT NULL DEFAULT 1,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE playlist_videos (
    playlist_id     INT UNSIGNED NOT NULL,
    video_id        INT UNSIGNED NOT NULL,
    position        INT UNSIGNED NOT NULL DEFAULT 0,
    added_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (playlist_id, video_id),
    FOREIGN KEY (playlist_id) REFERENCES playlists(id) ON DELETE CASCADE,
    FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- MODERATION
-- ============================================================

CREATE TABLE reports (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reportable_type     ENUM('video', 'artist', 'tag') NOT NULL,
    reportable_id       INT UNSIGNED NOT NULL,
    reported_by         INT UNSIGNED NOT NULL,
    reason              ENUM('duplicate', 'wrong_info', 'spam', 'inappropriate', 'other') NOT NULL,
    comment             TEXT NULL,
    status              ENUM('pending', 'resolved', 'dismissed') NOT NULL DEFAULT 'pending',
    resolved_by         INT UNSIGNED NULL,
    resolved_at         DATETIME NULL,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reported_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_reports_status (status),
    INDEX idx_reports_target (reportable_type, reportable_id)
) ENGINE=InnoDB;

CREATE TABLE edit_history (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    editable_type   ENUM('video', 'artist', 'tag') NOT NULL,
    editable_id     INT UNSIGNED NOT NULL,
    editor_id       INT UNSIGNED NOT NULL,
    action          ENUM('create', 'update', 'delete') NOT NULL,
    diff_json       JSON NOT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (editor_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_history_target (editable_type, editable_id)
) ENGINE=InnoDB;

-- ============================================================
-- TRADUCTION AUTOMATIQUE (OpenAI, déclenchée par un admin)
-- ============================================================

CREATE TABLE translation_jobs (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    source_type     ENUM('video', 'artist') NOT NULL,
    source_id       INT UNSIGNED NOT NULL,
    source_lang     ENUM('fr', 'en', 'ja') NOT NULL,
    target_lang     ENUM('fr', 'en', 'ja') NOT NULL,
    status          ENUM('pending', 'done', 'failed') NOT NULL DEFAULT 'pending',
    triggered_by    INT UNSIGNED NOT NULL,
    error_message   VARCHAR(500) NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at    DATETIME NULL,
    FOREIGN KEY (triggered_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================
-- SUIVI D'ARTISTES (phase 2 - notifications, table créée par anticipation)
-- ============================================================

CREATE TABLE user_follows_artist (
    user_id     INT UNSIGNED NOT NULL,
    artist_id   INT UNSIGNED NOT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, artist_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (artist_id) REFERENCES artists(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- DONNEES INITIALES : catégories de tags fixes
-- ============================================================
-- Note : "Type de contenu" (MV / live / cover / lyric video...) est géré
-- par la colonne `videos.video_type` (ENUM), donc pas de catégorie de tag
-- dédiée. Catégories fixes : Genre, Langue, Label.

INSERT INTO tag_categories (id, slug) VALUES
    (1, 'genre'),
    (2, 'language'),
    (3, 'label');

INSERT INTO tag_categories_i18n (category_id, lang, name) VALUES
    (1, 'fr', 'Genre'),        (1, 'en', 'Genre'),        (1, 'ja', 'ジャンル'),
    (2, 'fr', 'Langue'),       (2, 'en', 'Language'),     (2, 'ja', '言語'),
    (3, 'fr', 'Label'),        (3, 'en', 'Label'),        (3, 'ja', 'レーベル');

-- ------------------------------------------------------------
-- Tags "Genre" (catégorie 1)
-- ------------------------------------------------------------
INSERT INTO tags (id, category_id, slug) VALUES
    (1,  1, 'jpop'),
    (2,  1, 'jrock'),
    (3,  1, 'city-pop'),
    (4,  1, 'vocaloid'),
    (5,  1, 'anison'),
    (6,  1, 'idol'),
    (7,  1, 'visual-kei'),
    (8,  1, 'enka'),
    (9,  1, 'jhiphop'),
    (10, 1, 'jmetal'),
    (11, 1, 'jindie'),
    (12, 1, 'denpa'),
    (13, 1, 'folk'),
    (14, 1, 'punk');

INSERT INTO tags_i18n (tag_id, lang, name) VALUES
    (1,  'fr', 'J-Pop'),                       (1,  'en', 'J-Pop'),                    (1,  'ja', 'J-POP'),
    (2,  'fr', 'J-Rock'),                      (2,  'en', 'J-Rock'),                   (2,  'ja', 'J-ROCK'),
    (3,  'fr', 'City Pop'),                    (3,  'en', 'City Pop'),                 (3,  'ja', 'シティ・ポップ'),
    (4,  'fr', 'Vocaloid'),                    (4,  'en', 'Vocaloid'),                 (4,  'ja', 'ボーカロイド'),
    (5,  'fr', 'Anison (chanson d\'anime)'),   (5,  'en', 'Anison (anime song)'),      (5,  'ja', 'アニソン'),
    (6,  'fr', 'Idol'),                        (6,  'en', 'Idol'),                     (6,  'ja', 'アイドル'),
    (7,  'fr', 'Visual Kei'),                  (7,  'en', 'Visual Kei'),               (7,  'ja', 'ヴィジュアル系'),
    (8,  'fr', 'Enka'),                        (8,  'en', 'Enka'),                     (8,  'ja', '演歌'),
    (9,  'fr', 'Hip-Hop japonais'),            (9,  'en', 'Japanese Hip-Hop'),         (9,  'ja', '日本語ヒップホップ'),
    (10, 'fr', 'Metal japonais'),              (10, 'en', 'Japanese Metal'),           (10, 'ja', 'ジャパニーズメタル'),
    (11, 'fr', 'Indie / Shoegaze japonais'),   (11, 'en', 'Japanese Indie / Shoegaze'),(11, 'ja', '日本のインディー'),
    (12, 'fr', 'Denpa / Électro'),             (12, 'en', 'Denpa / Electronic'),       (12, 'ja', '電波系'),
    (13, 'fr', 'Folk / Acoustique'),           (13, 'en', 'Folk / Acoustic'),          (13, 'ja', 'フォーク'),
    (14, 'fr', 'Punk japonais'),               (14, 'en', 'Japanese Punk'),            (14, 'ja', 'パンク');

-- ------------------------------------------------------------
-- Tags "Langue" (catégorie 2)
-- ------------------------------------------------------------
INSERT INTO tags (id, category_id, slug) VALUES
    (15, 2, 'japanese'),
    (16, 2, 'english'),
    (17, 2, 'instrumental'),
    (18, 2, 'mixed-jp-en');

INSERT INTO tags_i18n (tag_id, lang, name) VALUES
    (15, 'fr', 'Japonais'),        (15, 'en', 'Japanese'),        (15, 'ja', '日本語'),
    (16, 'fr', 'Anglais'),         (16, 'en', 'English'),         (16, 'ja', '英語'),
    (17, 'fr', 'Instrumental'),    (17, 'en', 'Instrumental'),    (17, 'ja', 'インストゥルメンタル'),
    (18, 'fr', 'Mixte (JP/EN)'),   (18, 'en', 'Mixed (JP/EN)'),   (18, 'ja', '混合(日英)');

-- ------------------------------------------------------------
-- Tags "Label" (catégorie 3) : non pré-rempli.
-- Trop nombreux et spécifiques pour un seed générique ; à créer
-- au fil de l'eau depuis l'interface d'administration.
-- ------------------------------------------------------------
