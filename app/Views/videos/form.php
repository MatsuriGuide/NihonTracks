<h1><?= $mode === 'edit' ? e(t('videos.form_edit')) : e(t('videos.form_create')) ?></h1>

<?php if (!empty($errors)): ?>
    <ul class="errors">
        <?php foreach ($errors as $error): ?>
            <li><?= e($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if (!empty($old['thumbnail_url'])): ?>
    <img src="<?= e($old['thumbnail_url']) ?>" alt="" style="max-width: 320px; display:block; margin-bottom: 1em;">
<?php endif; ?>

<form method="post" action="<?= $mode === 'edit' ? url('/videos/' . $videoId . '/edit') : url('/videos/store') ?>">
    <input type="hidden" name="youtube_id" value="<?= e($old['youtube_id'] ?? '') ?>">
    <input type="hidden" name="youtube_url" value="<?= e($old['youtube_url'] ?? '') ?>">
    <input type="hidden" name="thumbnail_url" value="<?= e($old['thumbnail_url'] ?? '') ?>">
    <input type="hidden" name="channel_name" value="<?= e($old['channel_name'] ?? '') ?>">
    <input type="hidden" name="duration_seconds" value="<?= e((string) ($old['duration_seconds'] ?? '')) ?>">

    <p>
        <label for="title"><?= e(t('videos.title_label')) ?></label><br>
        <input type="text" id="title" name="title" value="<?= e($old['title'] ?? '') ?>" required
               style="width: 100%; max-width: 500px;">
    </p>

    <p>
        <label for="release_date"><?= e(t('videos.release_date')) ?></label><br>
        <input type="date" id="release_date" name="release_date" value="<?= e($old['release_date'] ?? '') ?>">
    </p>

    <p>
        <label for="video_type"><?= e(t('videos.type')) ?></label><br>
        <select id="video_type" name="video_type">
            <?php foreach (['mv', 'lyric_video', 'live', 'performance', 'cover', 'teaser', 'other'] as $value): ?>
                <option value="<?= e($value) ?>" <?= ($old['video_type'] ?? 'mv') === $value ? 'selected' : '' ?>>
                    <?= e(t('videos.type.' . $value)) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <fieldset>
        <legend><?= e(t('videos.artists_legend')) ?></legend>

        <?php if (!empty($autoDetected)): ?>
            <p class="hint hint-success"><?= e(t('videos.autodetected')) ?></p>
        <?php endif; ?>

        <?php if (empty($artists)): ?>
            <p><?= e(t('videos.no_artists')) ?> <a href="<?= url('/artists/create') ?>"><?= e(t('videos.create_artist')) ?></a></p>
        <?php else: ?>
            <?php if (count($artists) > 8): ?>
                <input type="text" id="artist-search" placeholder="<?= e(t('videos.search_artist')) ?>" autocomplete="off">
            <?php endif; ?>
            <div id="artist-checklist" class="checklist">
                <?php foreach ($artists as $artist): ?>
                    <label class="artist-option" data-name="<?= e(mb_strtolower($artist['name'] ?? $artist['slug'])) ?>">
                        <input type="checkbox" name="artist_ids[]" value="<?= (int) $artist['id'] ?>"
                            <?= in_array((int) $artist['id'], $selectedArtistIds, true) ? 'checked' : '' ?>>
                        <?= e($artist['name'] ?? $artist['slug']) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </fieldset>

    <?php foreach ($tagGroups as $categorySlug => $tags): ?>
        <fieldset>
            <legend><?= e(t('videos.tags_label')) ?> — <?= e(ucfirst($categorySlug)) ?></legend>
            <?php foreach ($tags as $tag): ?>
                <label>
                    <input type="checkbox" name="tag_ids[]" value="<?= (int) $tag['id'] ?>"
                        <?= in_array((int) $tag['id'], $selectedTagIds, true) ? 'checked' : '' ?>>
                    <?= e($tag['name'] ?? $tag['slug']) ?>
                </label><br>
            <?php endforeach; ?>
        </fieldset>
    <?php endforeach; ?>

    <button type="submit"><?= $mode === 'edit' ? e(t('videos.submit_edit')) : e(t('videos.submit_create')) ?></button>
</form>

<script src="<?= asset('js/artist-filter.js') ?>"></script>
