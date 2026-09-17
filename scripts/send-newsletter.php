<?php

/**
 * Script CLI déclenché par CRON une fois par jour : parcourt tous les
 * abonnés à la newsletter (au moins un filtre coché), leur envoie un
 * email récapitulatif s'il y a du nouveau depuis leur dernier envoi.
 *
 * IMPORTANT — compatibilité : ce script tourne en PHP CLI 7.4 sur cet
 * hébergement (contrairement au PHP 8.3 utilisé côté web), donc tout le
 * code qu'il charge doit rester compatible 7.4 (pas de types union, pas
 * de "catch (\Exception)" sans variable, etc.) — voir les autres scripts
 * CLI du projet pour ce même impératif.
 *
 * Exemple de configuration CRON (une fois par jour, 8h du matin) :
 * 0 8 * * *
 * /usr/bin/php /home/.../nihontracks/scripts/send-newsletter.php >> /home/.../nihontracks/storage/logs/cron-output.log 2>&1
 */

require dirname(__DIR__) . '/autoload.php';

\App\Core\Env::load(dirname(__DIR__) . '/.env');

try {
    $result = \App\Services\NewsletterDigestService::run();

    echo sprintf(
        "[%s] Newsletter : %d abonné(s) vérifié(s), %d email(s) envoyé(s), %d sans nouveauté, %d échec(s)\n",
        date('Y-m-d H:i:s'),
        $result['checked'],
        $result['sent'],
        $result['skipped_empty'],
        $result['failed']
    );
} catch (\Exception $e) {
    echo sprintf("[%s] Erreur newsletter : %s\n", date('Y-m-d H:i:s'), $e->getMessage());
    exit(1);
}
