<h1><?= e(t('videos.title')) ?></h1>

<?php if (in_array(\App\Core\Auth::role(), ['moderator', 'admin'], true)): ?>
    <p><a href="<?= url('/videos/create') ?>" class="btn"><?= e(t('videos.add')) ?></a></p>
<?php endif; ?>

<details open>
    <summary><?= e(t('videos.filter.title')) ?></summary>
    <form method="get" action="<?= url('/videos') ?>">
        <p>
            <label for="artist_id"><?= e(t('videos.filter.artist_label')) ?></label><br>
            <select id="artist_id" name="artist_id">
                <option value=""><?= e(t('videos.filter.all_artists')) ?></option>
                <?php foreach ($artists as $artist): ?>
                    <option value="<?= (int) $artist['id'] ?>" <?= $selectedArtistId === (int) $artist['id'] ? 'selected' : '' ?>>
                        <?= e($artist['name'] ?? $artist['slug']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <?php foreach ($tagGroups as $categorySlug => $tags): ?>
            <fieldset>
                <legend><?= e(t('videos.tags_label')) ?> — <?= e(ucfirst($categorySlug)) ?></legend>
                <?php foreach ($tags as $tag): ?>
                    <label>
                        <input type="checkbox" name="tag_ids[]" value="<?= (int) $tag['id'] ?>"
                            <?= in_array((int) $tag['id'], $selectedTagIds, true) ? 'checked' : '' ?>>
                        <?= e($tag['name'] ?? $tag['slug']) ?>
                    </label>
                <?php endforeach; ?>
            </fieldset>
        <?php endforeach; ?>

        <button type="submit"><?= e(t('videos.filter.apply')) ?></button>
        <?php if ($selectedArtistId !== null || !empty($selectedTagIds)): ?>
            <a href="<?= url('/videos') ?>"><?= e(t('videos.filter.reset')) ?></a>
        <?php endif; ?>
    </form>
</details>

<?php if (empty($videos)): ?>
    <p>
        <?php if ($selectedArtistId !== null || !empty($selectedTagIds)): ?>
            <?= e(t('videos.filter.no_results')) ?>
        <?php else: ?>
            <?= e(t('videos.empty')) ?>
        <?php endif; ?>
    </p>
<?php else: ?>
    <ul class="card-grid">
        <?php foreach ($videos as $video): ?>
            <li class="card">
                <?php if (!empty($video['thumbnail_url'])): ?>
                    <div class="card-thumb" style="background-image:url('<?= e($video['thumbnail_url']) ?>');"></div>
                <?php else: ?>
                    <div class="card-thumb"></div>
                <?php endif; ?>
                <div class="card-body">
                    <span class="catalog-no is-muted"><?= e(catalog_no('v', (int) $video['id'])) ?></span>
                    <a href="<?= url('/videos/' . $video['id']) ?>" class="card-title">
                        <?= e($video['title'] ?? $video['youtube_id']) ?>
                    </a>
                    <span class="card-meta">
                        <?php if (!empty($video['artist_names'])): ?>
                            <?= e($video['artist_names']) ?>
                        <?php endif; ?>
                        <?php if (!empty($video['release_date'])): ?>
                            — <?= e($video['release_date']) ?>
                        <?php endif; ?>
                    </span>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
