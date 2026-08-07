<h1><?= e($translation['title'] ?? $video['youtube_id']) ?></h1>

<p>
    <iframe width="560" height="315"
        src="https://www.youtube.com/embed/<?= e($video['youtube_id']) ?>"
        title="<?= e($translation['title'] ?? '') ?>" frameborder="0" allowfullscreen></iframe>
</p>

<p>
    <?= e(t('videos.type')) ?> : <?= e(t('videos.type.' . $video['video_type'])) ?>
    <?php if (!empty($video['release_date'])): ?>
        — <?= e(t('videos.release_date')) ?> : <?= e($video['release_date']) ?>
    <?php endif; ?>
    <?php if (!empty($video['channel_name'])): ?>
        — <?= e(t('videos.channel')) ?> : <?= e($video['channel_name']) ?>
    <?php endif; ?>
</p>

<?php if (!empty($artists)): ?>
    <p>
        <?= e(t('videos.artists_label')) ?> :
        <?php foreach ($artists as $i => $artist): ?>
            <?= $i > 0 ? ', ' : '' ?><?= e($artist['name'] ?? '') ?>
        <?php endforeach; ?>
    </p>
<?php endif; ?>

<?php if (!empty($tags)): ?>
    <p>
        <?= e(t('videos.tags_label')) ?> :
        <?php foreach ($tags as $tag): ?>
            <span class="tag"><?= e($tag['name'] ?? '') ?></span>
        <?php endforeach; ?>
    </p>
<?php endif; ?>

<?php if (\App\Core\Auth::check()): ?>
    <h2><?= e(t('videos.add_to_playlist')) ?></h2>
    <?php if (empty($userPlaylists)): ?>
        <p><?= e(t('videos.no_playlist')) ?> <a href="<?= url('/playlists/create') ?>"><?= e(t('videos.create_playlist')) ?></a></p>
    <?php else: ?>
        <?php foreach ($userPlaylists as $playlist): ?>
            <form method="post" action="<?= url('/playlists/' . $playlist['id'] . '/videos') ?>" style="display:inline">
                <input type="hidden" name="video_id" value="<?= (int) $video['id'] ?>">
                <button type="submit"><?= e($playlist['name']) ?></button>
            </form>
        <?php endforeach; ?>
    <?php endif; ?>
<?php endif; ?>

<?php if (\App\Core\Auth::canEdit((int) $video['added_by'])): ?>
    <p>
        <a href="<?= url('/videos/' . $video['id'] . '/edit') ?>"><?= e(t('videos.edit')) ?></a>
        &nbsp;
        <form method="post" action="<?= url('/videos/' . $video['id'] . '/delete') ?>"
              onsubmit="return confirm('<?= e(t('videos.delete_confirm')) ?>');" style="display:inline">
            <button type="submit"><?= e(t('videos.delete')) ?></button>
        </form>
    </p>
<?php endif; ?>

<?php if (\App\Core\Auth::check()): ?>
    <details>
        <summary><?= e(t('videos.report_this')) ?></summary>
        <form method="post" action="<?= url('/reports') ?>">
            <input type="hidden" name="reportable_type" value="video">
            <input type="hidden" name="reportable_id" value="<?= (int) $video['id'] ?>">
            <p>
                <label for="reason"><?= e(t('report.reason')) ?></label><br>
                <select id="reason" name="reason">
                    <option value="duplicate"><?= e(t('report.reason.duplicate')) ?></option>
                    <option value="wrong_info"><?= e(t('report.reason.wrong_info')) ?></option>
                    <option value="spam"><?= e(t('report.reason.spam')) ?></option>
                    <option value="inappropriate"><?= e(t('report.reason.inappropriate')) ?></option>
                    <option value="other"><?= e(t('report.reason.other')) ?></option>
                </select>
            </p>
            <p>
                <label for="comment"><?= e(t('report.comment')) ?></label><br>
                <textarea id="comment" name="comment" rows="3"></textarea>
            </p>
            <button type="submit"><?= e(t('report.submit')) ?></button>
        </form>
    </details>
<?php endif; ?>

<p><a href="<?= url('/videos') ?>"><?= e(t('videos.back')) ?></a></p>
