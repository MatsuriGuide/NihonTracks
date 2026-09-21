<?php
$noticeLabels = [
    'fr' => null,
    'en' => 'This FAQ page is provided in French only. Use your browser\'s translation feature if needed.',
    'ja' => 'このFAQページはフランス語のみで提供されています。必要に応じてブラウザの翻訳機能をご利用ください。',
];
$notice = $noticeLabels[\App\Core\Lang::current()] ?? null;
?>

<h1>Foire aux questions</h1>

<?php if ($notice): ?>
    <p class="hint"><?= e($notice) ?></p>
<?php endif; ?>

<h2>Qu'est-ce que NihonTracks ?</h2>
<p>
    NihonTracks est un catalogue collaboratif de vidéos musicales japonaises (clips officiels, lives,
    covers, lyric videos...). Chaque vidéo est rattachée à un ou plusieurs artistes et peut être classée
    par tags (genre musical, langue, type de voix). Le contenu est ajouté et enrichi par sa communauté,
    sous la supervision de modérateurs et d'administrateurs.
</p>

<h2>Comment ajouter un artiste ?</h2>
<p>
    Depuis la liste des artistes, un lien permet d'en ajouter un nouveau, ou de faire un ajout rapide à
    partir d'une simple URL de chaîne YouTube (au format <span class="mono">/channel/UC...</span> ou
    <span class="mono">/@pseudo</span>) : le nom, la photo et le nombre d'abonnés sont alors récupérés
    automatiquement. Une fiche soumise par un utilisateur normal passe par une validation avant d'apparaître
    publiquement ; les modérateurs et administrateurs n'ont pas cette étape.
</p>

<h2>Comment ajouter une vidéo ?</h2>
<p>
    L'ajout manuel d'une vidéo est réservé aux modérateurs et administrateurs, en collant son URL YouTube :
    titre, miniature et durée sont récupérés automatiquement, et l'artiste est détecté quand c'est possible
    à partir de la chaîne YouTube. NihonTracks surveille aussi automatiquement les chaînes des artistes déjà
    enregistrés et publie leurs nouvelles vidéos sans intervention.
</p>

<h2>Comment fonctionnent les tags ?</h2>
<p>
    Les tags sont répartis en catégories (genre musical, langue, type de voix). Ceux d'un artiste sont
    copiés automatiquement sur ses nouvelles vidéos, modifiables ensuite indépendamment sur chacune. La
    liste des tags existants est gérée par les administrateurs, pour éviter les doublons ou variantes
    inutiles.
</p>

<h2>Comment fonctionnent les playlists ?</h2>
<p>
    Tout utilisateur connecté peut créer des playlists, publiques ou privées, et y ajouter des vidéos
    directement depuis leur fiche. Une playlist publique peut être lue en continu directement sur le site,
    vidéo après vidéo.
</p>

<h2>Comment fonctionne la newsletter ?</h2>
<p>
    Depuis la liste des vidéos, tout utilisateur connecté peut enregistrer un ou plusieurs filtres
    (par artiste, type de vidéo ou tags) et cocher lesquels doivent être envoyés par email. Une fois par
    jour, si de nouvelles vidéos correspondent à au moins un des filtres cochés, un email récapitulatif est
    envoyé — jamais de doublon si une même vidéo correspond à plusieurs filtres, et jamais d'email s'il n'y
    a rien de nouveau. Un lien de désabonnement en un clic est présent dans chaque email.
</p>

<h2>Le site est-il disponible dans d'autres langues ?</h2>
<p>
    L'interface est disponible en français, anglais et japonais (sélecteur de langue en haut de page). Les
    fiches artistes et vidéos peuvent avoir une traduction dans chacune de ces langues ; à défaut, la
    version française sert de repli.
</p>

<h2>Comment signaler une erreur ou un contenu inapproprié ?</h2>
<p>
    Chaque fiche artiste et chaque vidéo dispose d'un lien « Signaler ». Le signalement est ensuite traité
    par un modérateur ou un administrateur, qui peut corriger, masquer ou supprimer le contenu concerné.
</p>

<h2>Puis-je devenir modérateur ?</h2>
<p>
    Les rôles modérateur et administrateur sont attribués manuellement par l'éditeur du site. Si tu
    contribues régulièrement et que ça t'intéresse, écris à
    <a href="mailto:contact@koshiki.art">contact@koshiki.art</a>.
</p>

<h2>D'où viennent les informations affichées sur les vidéos et artistes ?</h2>
<p>
    Les métadonnées des vidéos (titre, miniature, durée, date de sortie) et des chaînes (photo, nombre
    d'abonnés) viennent directement de l'API YouTube. Les biographies, dates de formation et labels
    peuvent être complétés manuellement ou suggérés par une IA — toujours à vérifier avant d'être
    enregistrés, une IA pouvant se tromper.
</p>

<p><a href="<?= url('/') ?>">&larr; Retour à l'accueil</a></p>
