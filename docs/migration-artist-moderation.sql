-- ============================================================
-- NihonTracks - Validation des artistes soumis par des utilisateurs
-- ============================================================

ALTER TABLE artists
  ADD COLUMN moderation_status ENUM('approved', 'pending', 'rejected') NOT NULL DEFAULT 'approved' AFTER status;

-- Toutes les fiches déjà existantes passent automatiquement à "approved"
-- grâce à la valeur par défaut ci-dessus — aucun artiste actuel ne disparaît.

CREATE INDEX idx_artists_moderation_status ON artists (moderation_status);
