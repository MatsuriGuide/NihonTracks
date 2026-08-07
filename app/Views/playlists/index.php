<h1>Playlists publiques</h1>

<?php if (\App\Core\Auth::check()): ?>
    <p>
        <a href="<?= url('/playlists/mine') ?>">Mes playlists</a>
        &nbsp;—&nbsp;
        <a href="<?= url('/playlists/create') ?>">+ Créer une playlist</a>
    </p>
<?php endif; ?>

<?php if (empty($playlists)): ?>
    <p>Aucune playlist publique pour le moment.</p>
<?php else: ?>
    <ul>
        <?php foreach ($playlists as $playlist): ?>
            <li>
                <a href="<?= url('/playlists/' . $playlist['id']) ?>"><?= e($playlist['name']) ?></a>
                — par <?= e($playlist['owner_name']) ?>
                (<?= (int) $playlist['video_count'] ?> vidéo<?= (int) $playlist['video_count'] > 1 ? 's' : '' ?>)
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
