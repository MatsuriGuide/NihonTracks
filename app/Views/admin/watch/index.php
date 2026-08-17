<h1><?= e(t('admin.watch.title')) ?></h1>

<?php if ($scanResult !== null): ?>
    <p class="hint hint-success">
        <?= e(t('admin.watch.scan_result')) ?> <strong><?= (int) $scanResult ?></strong>
    </p>
<?php endif; ?>

<?php if (empty($channels)): ?>
    <p><?= e(t('admin.watch.none')) ?></p>
<?php else: ?>
    <ul>
        <?php foreach ($channels as $channel): ?>
            <li>
                <a href="<?= url('/artists/' . $channel['artist_slug']) ?>"><?= e($channel['artist_name'] ?? '') ?></a>
                —
                <?php if ($channel['channel_id']): ?>
                    <code><?= e($channel['channel_id']) ?></code>
                    <form method="post" action="<?= url('/admin/watch/' . $channel['link_id'] . '/scan') ?>" style="display:inline">
                        <button type="submit" class="btn-small"><?= e(t('admin.watch.scan_now')) ?></button>
                    </form>
                <?php else: ?>
                    <small><?= e(t('admin.watch.invalid_format')) ?></small>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<p><a href="<?= url('/admin') ?>"><?= e(t('admin.back_to_dashboard')) ?></a></p>
