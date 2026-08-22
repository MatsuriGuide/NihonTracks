<h1><span class="catalog-no"><?= e(catalog_no('v', (int) $video['id'])) ?></span><?= e($translation['title'] ?? $video['youtube_id']) ?></h1>

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
            <?= $i > 0 ? ', ' : '' ?><a href="<?= url('/artists/' . $artist['slug']) ?>"><?= e($artist['name'] ?? '') ?></a>
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
    <style>
        .playlist-toggle-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin: 0 0 1rem;
        }
        .playlist-toggle-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            background: transparent;
            border: 1px solid var(--line, #333);
            color: var(--ink-dim, #999);
        }
        .playlist-toggle-btn .check {
            display: none;
        }
        .playlist-toggle-btn.is-added {
            border-color: var(--mint, #38d6b4);
            color: var(--mint, #38d6b4);
            background: rgba(56, 214, 180, 0.08);
        }
        .playlist-toggle-btn.is-added .check {
            display: inline;
        }
    </style>

    <h2><?= e(t('videos.add_to_playlist')) ?></h2>
    <?php if (empty($userPlaylists)): ?>
        <p><?= e(t('videos.no_playlist')) ?> <a href="<?= url('/playlists/create') ?>"><?= e(t('videos.create_playlist')) ?></a></p>
    <?php else: ?>
        <div class="playlist-toggle-list">
            <?php foreach ($userPlaylists as $playlist): ?>
                <button type="button" class="playlist-toggle-btn<?= !empty($playlist['has_video']) ? ' is-added' : '' ?>"
                        data-toggle-url="<?= url('/playlists/' . $playlist['id'] . '/videos/' . $video['id'] . '/toggle') ?>">
                    <span class="check">✓</span> <?= e($playlist['name']) ?>
                </button>
            <?php endforeach; ?>
        </div>
        <script src="<?= asset('js/playlist-toggle.js') ?>"></script>
    <?php endif; ?>
<?php endif; ?>

<?php if (\App\Core\Auth::role() === 'admin'): ?>
    <div class="admin-translate">
        <h3><?= e(t('admin.translate.title')) ?></h3>
        <?php foreach (['en', 'ja'] as $targetLang): ?>
            <?php $existing = $allTranslations[$targetLang] ?? null; ?>
            <form method="post" action="<?= url('/admin/translate/video/' . $video['id'] . '/' . $targetLang) ?>" style="display:inline">
                <button type="submit">
                    <?= $existing ? e(t('admin.translate.retranslate')) : e(t('admin.translate.translate')) ?>
                    (<?= e(strtoupper($targetLang)) ?>)
                </button>
            </form>
            <?php if (!empty($existing['is_auto_translated'])): ?>
                <small><?= e(t('admin.translate.auto_flag')) ?></small>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
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
