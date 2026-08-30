<?php
$noticeLabels = [
    'fr' => null,
    'en' => 'This page (legal notice & privacy policy) is provided in French only, as required by French law for the site\'s legal publisher. Use your browser\'s translation feature if needed.',
    'ja' => 'このページ（法的通知・プライバシーポリシー）はフランスの法律上の要件により、フランス語のみで提供されています。必要に応じてブラウザの翻訳機能をご利用ください。',
];
$notice = $noticeLabels[\App\Core\Lang::current()] ?? null;
?>

<h1>À propos</h1>

<?php if ($notice): ?>
    <p class="hint"><?= e($notice) ?></p>
<?php endif; ?>

<p>
    NihonTracks est un catalogue collaboratif de vidéos musicales japonaises (clips, lives, covers...),
    avec fiches artistes, tags et playlists. Le contenu est ajouté et enrichi par sa communauté
    d'utilisateurs, sous la supervision de modérateurs et administrateurs.
</p>

<h2>Mentions légales</h2>

<h3>Éditeur du site</h3>
<p>
    Le site NihonTracks est édité à titre personnel et non professionnel par :<br>
    Cédrick Perron<br>
    Contact : <a href="mailto:contact@koshiki.art">contact@koshiki.art</a>
</p>

<h3>Hébergement</h3>
<p>
    Le site est hébergé par :<br>
    Hostinger International Ltd<br>
    61 Lordou Vironos Street, 6023 Larnaca, Chypre<br>
    <a href="https://www.hostinger.fr/contact" target="_blank" rel="noopener">https://www.hostinger.fr/contact</a>
</p>

<h3>Propriété intellectuelle</h3>
<p>
    La structure générale du site, ainsi que les textes, codes et éléments graphiques qui lui sont propres,
    sont la propriété de l'éditeur, sauf mention contraire. Les vidéos référencées restent la propriété de
    leurs ayants droit respectifs et sont simplement indexées via des liens vers la plateforme YouTube ;
    aucun contenu vidéo n'est hébergé directement par NihonTracks.
</p>

<h3>Contenu soumis par les utilisateurs</h3>
<p>
    Les fiches artistes, vidéos, tags et playlists sont ajoutés par les membres du site. Chaque contribution
    reste sous la responsabilité de son auteur. Un système de signalement et de modération permet de traiter
    les contenus erronés, inappropriés ou en infraction avec les droits de tiers ; toute demande de retrait
    peut être adressée à <a href="mailto:contact@koshiki.art">contact@koshiki.art</a>.
</p>

<h3>Limitation de responsabilité</h3>
<p>
    L'éditeur s'efforce d'assurer l'exactitude des informations diffusées sur le site, sans garantir
    l'absence d'erreurs ou d'omissions, notamment sur les contenus soumis par des tiers. L'éditeur ne
    saurait être tenu responsable des dysfonctionnements liés à l'hébergement, au réseau Internet, ou à
    l'indisponibilité temporaire du service.
</p>

<h3>Utilisation de l'API YouTube</h3>
<p>
    Ce site utilise les services de l'API YouTube (YouTube API Services). En l'utilisant, vous acceptez
    d'être lié par les
    <a href="https://www.youtube.com/t/terms" target="_blank" rel="noopener">conditions d'utilisation de YouTube</a>.
    Le traitement des données par Google est régi par sa
    <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">politique de confidentialité</a>.
</p>

<h2>Politique de confidentialité (RGPD)</h2>

<h3>Données collectées</h3>
<ul>
    <li>À l'inscription : adresse email, mot de passe (haché, jamais stocké en clair), nom affiché, langue préférée</li>
    <li>Contenu que vous créez : fiches artistes, vidéos ajoutées, tags, playlists, signalements</li>
    <li>Journal technique : en cas de tentative d'accès direct non autorisée à certains scripts internes, l'adresse IP est brièvement journalisée à des fins de sécurité</li>
    <li>Cookie de session : strictement nécessaire au fonctionnement (maintien de la connexion)</li>
    <li>Mesure d'audience : un cookie Matomo peut être déposé pour distinguer les visites d'une session à l'autre (voir « Mesure d'audience » ci-dessous)</li>
</ul>

<h3>Mesure d'audience</h3>
<p>
    Ce site utilise Matomo, un outil de mesure d'audience que l'éditeur héberge lui-même
    (<span class="mono">stats.matsuri-guide.fr</span>), pour analyser la fréquentation générale du site
    (nombre de visites, pages consultées, provenance). Aucune donnée n'est partagée avec des régies
    publicitaires ni revendue à des tiers, et les adresses IP des visiteurs sont anonymisées. Ces
    statistiques ne sont pas croisées avec votre compte utilisateur.
</p>
<p>
    Configuré ainsi (outil auto-hébergé, aucune donnée transmise à un tiers, adresses IP anonymisées),
    cet outil de mesure d'audience entre dans le cadre de l'exemption de consentement prévue par la CNIL
    pour les solutions d'analyse strictement limitées à la mesure d'audience du site — aucun bandeau de
    consentement cookies n'est donc affiché pour cet usage.
</p>

<h3>Finalités</h3>
<p>
    Ces données servent uniquement à faire fonctionner le site : création et gestion de votre compte,
    attribution de vos contributions, modération, personnalisation de la langue d'affichage, et
    compréhension générale de la fréquentation du site.
</p>

<h3>Destinataires et sous-traitants</h3>
<ul>
    <li><strong>Hostinger</strong> (hébergement) : hébergement technique des données</li>
    <li><strong>Matomo</strong> (auto-hébergé par l'éditeur, stats.matsuri-guide.fr) : mesure d'audience, aucune donnée transmise à un tiers externe</li>
    <li><strong>YouTube Data API (Google)</strong> : requêtes envoyées pour récupérer des métadonnées de vidéos et chaînes publiques (titres, miniatures, dates) — aucune donnée personnelle de compte n'est transmise à Google dans ce cadre</li>
    <li><strong>OpenAI</strong> : lorsqu'un administrateur déclenche une traduction automatique, le contenu textuel de la fiche concernée (nom, bio, titre) est envoyé à l'API OpenAI pour traduction — aucune donnée de compte utilisateur n'y est transmise</li>
</ul>

<h3>Durée de conservation</h3>
<p>
    Les données de votre compte sont conservées tant que celui-ci existe. Vous pouvez demander la
    suppression de votre compte et de vos données personnelles à tout moment.
</p>

<h3>Vos droits</h3>
<p>
    Conformément au RGPD, vous disposez d'un droit d'accès, de rectification, d'effacement, de limitation
    et d'opposition concernant vos données personnelles. Pour exercer ces droits, contactez
    <a href="mailto:contact@koshiki.art">contact@koshiki.art</a>. Vous disposez également du droit
    d'introduire une réclamation auprès de la CNIL (www.cnil.fr).
</p>

<h3>Sécurité</h3>
<p>
    Les mots de passe sont hachés (bcrypt) et jamais stockés en clair. L'accès aux fonctions
    d'administration est restreint par rôle (utilisateur, modérateur, administrateur).
</p>

<p><a href="<?= url('/') ?>">&larr; Retour à l'accueil</a></p>
