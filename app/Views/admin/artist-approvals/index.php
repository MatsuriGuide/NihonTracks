<h1><?= e(t('admin.artist_approvals.title')) ?></h1>

<?php if (empty($pending)): ?>
    <p><?= e(t('admin.artist_approvals.none')) ?></p>
<?php else: ?>
    <ul>
        <?php foreach ($pending as $artist): ?>
            <li>
                <a href="<?= url('/artists/' . $artist['slug']) ?>" target="_blank"><?= e($artist['name'] ?? $artist['slug']) ?></a>
                (<?= e(t('artists.type.' . $artist['type'])) ?>)
                — <?= e(t('admin.artist_approvals.submitted_by')) ?> <?= e($artist['submitted_by_name'] ?? '?') ?>
                — <?= e($artist['created_at']) ?>

                <form method="post" action="<?= url('/admin/artist-approvals/' . $artist['id'] . '/approve') ?>" style="display:inline">
                    <button type="submit"><?= e(t('admin.artist_approvals.approve')) ?></button>
                </form>
                <form method="post" action="<?= url('/admin/artist-approvals/' . $artist['id'] . '/reject') ?>"
                      onsubmit="return confirm('<?= e(t('admin.artist_approvals.reject_confirm')) ?>');" style="display:inline">
                    <button type="submit"><?= e(t('admin.artist_approvals.reject')) ?></button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<p><a href="<?= url('/admin') ?>"><?= e(t('admin.back_to_dashboard')) ?></a></p>
