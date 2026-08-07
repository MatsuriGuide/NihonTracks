<h1><?= e(t('playlists.mine_title')) ?></h1>

<p>
    <a href="<?= url('/playlists/create') ?>"><?= e(t('playlists.create_link')) ?></a>
    &nbsp;—&nbsp;
    <a href="<?= url('/playlists') ?>"><?= e(t('playlists.public_link')) ?></a>
</p>

<?php if (empty($playlists)): ?>
    <p><?= e(t('playlists.empty_mine')) ?></p>
<?php else: ?>
    <ul>
        <?php foreach ($playlists as $playlist): ?>
            <li>
                <a href="<?= url('/playlists/' . $playlist['id']) ?>"><?= e($playlist['name']) ?></a>
                <?= $playlist['is_public'] ? e(t('playlists.public')) : e(t('playlists.private')) ?>
                — <?= (int) $playlist['video_count'] ?> <?= e(t('playlists.video_count')) ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
