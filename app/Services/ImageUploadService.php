<?php

namespace App\Services;

use RuntimeException;

/**
 * Téléversement des photos d'artistes : validation, correction d'orientation, réduction et ré-encodage en JPEG
 * (ce qui élimine tout contenu étranger caché dans le fichier envoyé).
 */
class ImageUploadService
{
    public const MAX_BYTES = 12 * 1024 * 1024;       // 12 Mo envoyés
    public const MAX_PIXELS = 40_000_000;            // 40 mégapixels (mémoire nécessaire au décodage)
    public const MAX_SIDE = 1400;                    // côté maximal après réduction
    public const SUBDIR = 'uploads/artists';

    private static function baseDir(): string
    {
        return dirname(__DIR__, 2) . '/public/' . self::SUBDIR;
    }

    /**
     * Enregistre la photo envoyée et renvoie son chemin public relatif (ex. « uploads/artists/artist-60-ab12cd.jpg »).
     *
     * @param array $file élément de $_FILES
     * @throws RuntimeException le message est une clé : « format », « size » ou « upload »
     */
    public static function saveArtistPhoto(array $file, int $artistId, bool $checkUploaded = true): string
    {
        if (!function_exists('imagecreatefromstring') || !function_exists('imagejpeg')) {
            throw new RuntimeException('upload');
        }

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException(
                in_array($file['error'] ?? 0, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true) ? 'size' : 'upload'
            );
        }

        $tmp = (string) ($file['tmp_name'] ?? '');

        if ($tmp === '' || ($checkUploaded && !is_uploaded_file($tmp))) {
            throw new RuntimeException('upload');
        }

        if ((int) ($file['size'] ?? 0) > self::MAX_BYTES) {
            throw new RuntimeException('size');
        }

        $info = @getimagesize($tmp);

        if ($info === false || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            throw new RuntimeException('format');
        }

        if ($info[0] * $info[1] > self::MAX_PIXELS) {
            throw new RuntimeException('size');
        }

        $raw = file_get_contents($tmp);
        $image = $raw === false ? false : @imagecreatefromstring($raw);

        if ($image === false) {
            throw new RuntimeException('format');
        }

        // Orientation EXIF (photos de téléphone)
        if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $exif = @exif_read_data($tmp);
            $rotation = [3 => 180, 6 => -90, 8 => 90][$exif['Orientation'] ?? 1] ?? 0;

            if ($rotation !== 0) {
                $rotated = imagerotate($image, $rotation, 0);

                if ($rotated !== false) {
                    $image = $rotated;
                }
            }
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $scale = min(1.0, self::MAX_SIDE / max($width, $height));
        $newWidth = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));

        // Fond blanc (PNG / WebP transparents) et réduction
        $canvas = imagecreatetruecolor($newWidth, $newHeight);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        $dir = self::baseDir();

        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('upload');
        }

        // Jamais d'exécution de PHP dans ce dossier, par précaution
        $htaccess = $dir . '/.htaccess';

        if (!is_file($htaccess)) {
            @file_put_contents($htaccess, "<FilesMatch \"\\.(php|phtml|phar)$\">\n    Require all denied\n</FilesMatch>\n");
        }

        $name = sprintf('artist-%d-%s.jpg', $artistId, bin2hex(random_bytes(6)));

        if (!imagejpeg($canvas, $dir . '/' . $name, 86)) {
            throw new RuntimeException('upload');
        }

        return self::SUBDIR . '/' . $name;
    }

    /**
     * Supprime l'ancienne photo si c'est un de nos fichiers téléversés (jamais une image externe).
     */
    public static function deleteIfOurs(?string $avatarUrl): void
    {
        if ($avatarUrl === null || preg_match('#/' . preg_quote(self::SUBDIR, '#') . '/(artist-\d+-[0-9a-f]{12}\.jpg)$#', $avatarUrl, $m) !== 1) {
            return;
        }

        $path = self::baseDir() . '/' . $m[1];

        if (is_file($path)) {
            @unlink($path);
        }
    }
}
