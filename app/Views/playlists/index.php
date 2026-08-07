<h1><?= e(t('playlists.public_title')) ?></h1>

<?php if (\App\Core\Auth::check()): ?>
    <p>
        <a href="<?= url('/playlists/mine') ?>"><?= e(t('playlists.mine_link')) ?></a>
        &nbsp;—&nbsp;
        <a href="<?= url('/playlists/create') ?>"><?= e(t('playlists.create_link')) ?></a>
    </p>
<?php endif; ?>

<?php if (empty($playlists)): ?>
    <p><?= e(t('playlists.empty_public')) ?></p>
<?php else: ?>
    <ul>
        <?php foreach ($playlists as $playlist): ?>
            <li>
                <a href="<?= url('/playlists/' . $playlist['id']) ?>"><?= e($playlist['name']) ?></a>
                — <?= e(t('playlists.by')) ?> <?= e($playlist['owner_name']) ?>
                (<?= (int) $playlist['video_count'] ?> <?= e(t('playlists.video_count')) ?>)
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
