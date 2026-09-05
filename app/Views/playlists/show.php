<h1><?= e($playlist['name']) ?></h1>

<?php if (!empty($playlist['description'])): ?>
    <p><?= nl2br(e($playlist['description'])) ?></p>
<?php endif; ?>

<p>
    <?= e(t('playlists.visibility')) ?> :
    <?= $playlist['is_public'] ? e(t('playlists.public')) : e(t('playlists.private')) ?>
</p>

<?php if ($canEdit): ?>
    <p>
        <a href="<?= url('/playlists/' . $playlist['id'] . '/edit') ?>"><?= e(t('playlists.edit')) ?></a>
        &nbsp;
        <form method="post" action="<?= url('/playlists/' . $playlist['id'] . '/delete') ?>"
              onsubmit="return confirm('<?= e(t('playlists.delete_confirm')) ?>');" style="display:inline">
            <button type="submit"><?= e(t('playlists.delete')) ?></button>
        </form>
    </p>

    <details>
        <summary><?= e(t('playlists.add_video_title')) ?></summary>
        <p>
            <label for="playlist-video-search"><?= e(t('playlists.add_video_search_label')) ?></label><br>
            <input type="text" id="playlist-video-search" autocomplete="off"
                   placeholder="<?= e(t('playlists.add_video_search_placeholder')) ?>"
                   data-search-url="<?= url('/playlists/' . $playlist['id'] . '/videos/search') ?>"
                   data-add-url="<?= url('/playlists/' . $playlist['id'] . '/videos') ?>"
                   data-add-label="<?= e(t('playlists.add_video_button')) ?>"
                   style="width: 100%; max-width: 400px;">
        </p>
        <ul id="playlist-video-search-results" class="card-grid"></ul>
    </details>

    <script src="<?= asset('js/playlist-video-search.js') ?>"></script>
<?php endif; ?>

<?php if (empty($videos)): ?>
    <p><?= e(t('playlists.empty')) ?></p>
<?php else: ?>
    <p>
        <button type="button" id="playlist-play-all-btn" class="btn"
                data-video-ids='<?= e(json_encode(array_column($videos, "youtube_id"))) ?>'>
            <?= e(t('playlists.play_all')) ?>
        </button>
    </p>

    <div id="playlist-sticky-player" style="display:none; position:sticky; top:0; z-index:10; background:var(--navy-950, #10122a); padding:0.75rem 0; margin-bottom:1rem;">
        <div id="playlist-player-iframe"></div>
    </div>

    <ul class="card-grid">
        <?php foreach ($videos as $video): ?>
            <li class="card">
                <?php if (!empty($video['thumbnail_url'])): ?>
                    <div class="card-thumb" style="background-image:url('<?= e($video['thumbnail_url']) ?>');"></div>
                <?php else: ?>
                    <div class="card-thumb"></div>
                <?php endif; ?>
                <div class="card-body">
                    <a href="<?= url('/videos/' . $video['id']) ?>" class="card-title">
                        <?= e($video['title'] ?? $video['youtube_id']) ?>
                    </a>
                    <span class="card-meta">
                        <?php if (!empty($video['artist_names'])): ?>
                            <?= e($video['artist_names']) ?>
                        <?php endif; ?>
                    </span>
                    <?php if ($canEdit): ?>
                        <form method="post" action="<?= url('/playlists/' . $playlist['id'] . '/videos/' . $video['id'] . '/remove') ?>" style="display:inline">
                            <button type="submit" class="btn-small"><?= e(t('playlists.remove_video')) ?></button>
                        </form>
                    <?php endif; ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>

    <script src="<?= asset('js/playlist-player.js') ?>"></script>
<?php endif; ?>

<p><a href="<?= url('/playlists') ?>"><?= e(t('playlists.back')) ?></a></p>
