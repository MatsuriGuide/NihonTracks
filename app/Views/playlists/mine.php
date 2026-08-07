<h1>Mes playlists</h1>

<p>
    <a href="<?= url('/playlists/create') ?>">+ Créer une playlist</a>
    &nbsp;—&nbsp;
    <a href="<?= url('/playlists') ?>">Voir les playlists publiques</a>
</p>

<?php if (empty($playlists)): ?>
    <p>Tu n'as pas encore de playlist.</p>
<?php else: ?>
    <ul>
        <?php foreach ($playlists as $playlist): ?>
            <li>
                <a href="<?= url('/playlists/' . $playlist['id']) ?>"><?= e($playlist['name']) ?></a>
                <?= $playlist['is_public'] ? '(publique)' : '(privée)' ?>
                — <?= (int) $playlist['video_count'] ?> vidéo<?= (int) $playlist['video_count'] > 1 ? 's' : '' ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
