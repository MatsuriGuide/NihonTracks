<?php

namespace App\Services;

require_once dirname(__DIR__) . '/Vendor/PHPMailer/Exception.php';
require_once dirname(__DIR__) . '/Vendor/PHPMailer/PHPMailer.php';
require_once dirname(__DIR__) . '/Vendor/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Enveloppe autour de PHPMailer (fichiers vendorisés manuellement dans
 * app/Vendor/PHPMailer — pas de Composer disponible en prod), configuré
 * pour envoyer via le SMTP authentifié de Hostinger plutôt que mail() PHP
 * (bien meilleure délivrabilité, mail() étant limité/souvent filtré comme
 * spam sur l'hébergement mutualisé).
 *
 * Configuration attendue dans .env :
 *   SMTP_HOST=smtp.hostinger.com
 *   SMTP_PORT=587
 *   SMTP_ENCRYPTION=tls        (tls pour le port 587, ssl pour le port 465)
 *   SMTP_USERNAME=newsletter@koshiki.art
 *   SMTP_PASSWORD=...
 *   SMTP_FROM_EMAIL=newsletter@koshiki.art
 *   SMTP_FROM_NAME=NihonTracks
 */
class NewsletterMailerService
{
    /**
     * Dernier message d'erreur PHPMailer rencontré — consultable après un
     * send() ayant retourné false, pour le diagnostic (/admin/diagnostic/smtp).
     * Ne fait rien d'autre que mémoriser la dernière tentative ; pas
     * partagé entre requêtes.
     */
    private static ?string $lastError = null;

    public static function lastError(): ?string
    {
        return self::$lastError;
    }

    public static function send(string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody): bool
    {
        self::$lastError = null;

        $host = $_ENV['SMTP_HOST'] ?? '';
        $username = $_ENV['SMTP_USERNAME'] ?? '';
        $password = $_ENV['SMTP_PASSWORD'] ?? '';

        if ($host === '' || $username === '' || $password === '') {
            self::$lastError = 'Configuration SMTP incomplète dans .env (SMTP_HOST/SMTP_USERNAME/SMTP_PASSWORD manquant).';

            return false;
        }

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $host;
            $mail->SMTPAuth = true;
            $mail->Username = $username;
            $mail->Password = $password;
            $mail->SMTPSecure = ($_ENV['SMTP_ENCRYPTION'] ?? 'tls') === 'ssl'
                ? PHPMailer::ENCRYPTION_SMTPS
                : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = (int) ($_ENV['SMTP_PORT'] ?? 587);
            $mail->CharSet = 'UTF-8';
            $mail->Timeout = 20;

            $mail->setFrom(
                $_ENV['SMTP_FROM_EMAIL'] ?? $username,
                $_ENV['SMTP_FROM_NAME'] ?? 'NihonTracks'
            );
            $mail->addAddress($toEmail, $toName);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = $textBody;

            $mail->send();

            return true;
        } catch (PHPMailerException $e) {
            self::$lastError = $mail->ErrorInfo;
            error_log('[Newsletter] Échec envoi à ' . $toEmail . ' : ' . $mail->ErrorInfo);

            return false;
        }
    }
}
