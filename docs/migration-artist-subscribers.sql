-- ============================================================
-- NihonTracks - Nombre d'abonnés YouTube sur les artistes
-- ============================================================

ALTER TABLE artists
  ADD COLUMN subscriber_count INT UNSIGNED NULL AFTER avatar_path;
