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
                    <?= e(t('admin.watch.scan_now')) ?>
                    <form method="post" action="<?= url('/admin/watch/' . $channel['link_id'] . '/scan') ?>" style="display:inline">
                        <button type="submit" name="limit" value="25" class="btn-small">25</button>
                        <button type="submit" name="limit" value="50" class="btn-small">50</button>
                        <button type="submit" name="limit" value="100" class="btn-small">100</button>
                    </form>
                <?php else: ?>
                    <small><?= e(t('admin.watch.invalid_format')) ?></small>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<h2><?= e(t('admin.watch.log_title')) ?></h2>

<?php if (empty($scanLog)): ?>
    <p><?= e(t('admin.watch.log_empty')) ?></p>
<?php else: ?>
    <ul>
        <?php foreach ($scanLog as $entry): ?>
            <?php $details = json_decode($entry['details'] ?? '', true) ?: []; ?>
            <li>
                <span class="mono"><?= e($entry['run_at']) ?></span> —
                <?php if ($entry['source'] === 'blocked_access'): ?>
                    <?= e(t('admin.watch.log_blocked')) ?> <code><?= e($details['ip'] ?? '?') ?></code>
                <?php else: ?>
                    <?= e($entry['source'] === 'cron' ? t('admin.watch.log_source_cron') : t('admin.watch.log_source_manual')) ?>
                    — <?= (int) $entry['channels_scanned'] ?> <?= e(t('admin.watch.log_channels')) ?>,
                    <?= (int) $entry['suggestions_found'] ?> <?= e(t('admin.watch.log_found')) ?>
                    <?php if ((int) $entry['errors_count'] > 0): ?>
                        — <strong><?= (int) $entry['errors_count'] ?> <?= e(t('admin.watch.log_errors')) ?></strong>
                    <?php endif; ?>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<p><a href="<?= url('/admin') ?>"><?= e(t('admin.back_to_dashboard')) ?></a></p>
