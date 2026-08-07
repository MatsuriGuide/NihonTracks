<h1><span class="catalog-no"><?= e(catalog_no('p', (int) $playlist['id'])) ?></span><?= e($playlist['name']) ?></h1>

<?php if (!empty($playlist['description'])): ?>
    <p><?= nl2br(e($playlist['description'])) ?></p>
<?php endif; ?>

<p><?= $playlist['is_public'] ? e(t('playlists.is_public_state')) : e(t('playlists.is_private_state')) ?></p>

<?php if (\App\Core\Auth::canEdit((int) $playlist['user_id'])): ?>
    <p>
        <a href="<?= url('/playlists/' . $playlist['id'] . '/edit') ?>"><?= e(t('playlists.edit')) ?></a>
        &nbsp;
        <form method="post" action="<?= url('/playlists/' . $playlist['id'] . '/delete') ?>"
              onsubmit="return confirm('<?= e(t('playlists.delete_confirm')) ?>');" style="display:inline">
            <button type="submit"><?= e(t('playlists.delete')) ?></button>
        </form>
    </p>
<?php endif; ?>

<h2><?= e(t('playlists.videos_title')) ?></h2>

<?php if (empty($videos)): ?>
    <p><?= e(t('playlists.no_videos')) ?></p>
<?php else: ?>
    <div class="playlist-player" id="playlist-player-panel">
        <div id="playlist-player"></div>
        <p class="now-playing" id="now-playing-label"></p>
    </div>

    <ol class="tracklist" id="playlist-tracklist">
        <?php foreach ($videos as $i => $video): ?>
            <li class="track" data-youtube-id="<?= e($video['youtube_id']) ?>">
                <button type="button" class="track-play" data-index="<?= (int) $i ?>">
                    <span class="track-number"><?= (int) $i + 1 ?></span>
                    <span class="track-title"><?= e($video['title'] ?? $video['youtube_id']) ?></span>
                    <?php if (!empty($video['artist_names'])): ?>
                        <span class="track-artist"><?= e($video['artist_names']) ?></span>
                    <?php endif; ?>
                </button>
                <a href="<?= url('/videos/' . $video['id']) ?>" class="track-link" title="<?= e(t('videos.title_label')) ?>">↗</a>
                <?php if (\App\Core\Auth::canEdit((int) $playlist['user_id'])): ?>
                    <form method="post"
                          action="<?= url('/playlists/' . $playlist['id'] . '/videos/' . $video['id'] . '/remove') ?>"
                          style="display:inline">
                        <button type="submit" class="track-remove"><?= e(t('playlists.remove')) ?></button>
                    </form>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>

    <script src="<?= asset('js/playlist-player.js') ?>"></script>
<?php endif; ?>

<?php if (\App\Core\Auth::canEdit((int) $playlist['user_id']) && !empty($availableVideos)): ?>
    <h2><?= e(t('playlists.add_video_title')) ?></h2>
    <form method="post" action="<?= url('/playlists/' . $playlist['id'] . '/videos') ?>">
        <select name="video_id" required>
            <option value=""><?= e(t('playlists.add_video_placeholder')) ?></option>
            <?php foreach ($availableVideos as $video): ?>
                <option value="<?= (int) $video['id'] ?>"><?= e($video['title'] ?? $video['youtube_id']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit"><?= e(t('playlists.add_video_submit')) ?></button>
    </form>
<?php endif; ?>

<p><a href="<?= url('/playlists') ?>"><?= e(t('playlists.back')) ?></a></p>
