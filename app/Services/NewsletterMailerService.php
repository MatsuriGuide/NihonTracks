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
            $port = (int) ($_ENV['SMTP_PORT'] ?? 587);

            $mail->isSMTP();
            $mail->Host = $host;
            $mail->SMTPAuth = true;
            $mail->Username = $username;
            $mail->Password = $password;
            // Le port 465 exige un TLS immédiat (SMTPS) dès l'ouverture de la
            // connexion ; le port 587 utilise STARTTLS (connexion en clair
            // puis mise à niveau). Les deux mécanismes sont incompatibles —
            // un mauvais réglage ne renvoie pas une erreur claire mais fait
            // geler la connexion jusqu'au timeout (symptôme observé : 504
            // Gateway Timeout plutôt qu'un message d'erreur). On déduit donc
            // le chiffrement du PORT en priorité, SMTP_ENCRYPTION ne servant
            // plus que pour un port non standard.
            $mail->SMTPSecure = ($port === 465 || ($_ENV['SMTP_ENCRYPTION'] ?? '') === 'ssl')
                ? PHPMailer::ENCRYPTION_SMTPS
                : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $port;
            $mail->CharSet = 'UTF-8';
            // Volontairement court : un dépassement doit remonter comme une
            // erreur claire plutôt que de faire attendre le serveur web
            // jusqu'à SON propre timeout (504 Gateway Timeout, sans aucun
            // message exploitable).
            $mail->Timeout = 10;

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
