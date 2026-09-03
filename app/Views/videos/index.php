<h1><?= e(t('videos.title')) ?></h1>

<?php if (in_array(\App\Core\Auth::role(), ['moderator', 'admin'], true)): ?>
    <p><a href="<?= url('/videos/create') ?>" class="btn"><?= e(t('videos.add')) ?></a></p>
<?php endif; ?>

<?php if (\App\Core\Auth::check() && !empty($savedPresets)): ?>
    <div class="hint">
        <strong><?= e(t('videos.filter.presets_title')) ?></strong><br>
        <?php foreach ($savedPresets as $preset): ?>
            <?php
                $presetParams = ['page' => 1];
                if ($preset['artist_id'] !== null) {
                    $presetParams['artist_id'] = (int) $preset['artist_id'];
                }
                if (!empty($preset['video_type'])) {
                    $presetParams['video_type'] = $preset['video_type'];
                }
                $presetTagIds = \App\Models\VideoFilterPreset::tagIds($preset);
            ?>
            <span style="display:inline-block; margin: 0.25rem 0.5rem 0.25rem 0;">
                <a href="<?= url('/videos?' . http_build_query($presetParams)) . implode('', array_map(static fn ($tid) => '&tag_ids[]=' . (int) $tid, $presetTagIds)) ?>">
                    <?= e($preset['name']) ?>
                </a>
                <?php if ((int) $preset['is_default'] === 1): ?>
                    <span class="tag"><?= e(t('videos.filter.is_default')) ?></span>
                    <form method="post" action="<?= url('/videos/filter-presets/clear-default') ?>" style="display:inline">
                        <button type="submit" class="btn-small"><?= e(t('videos.filter.unset_default')) ?></button>
                    </form>
                <?php else: ?>
                    <form method="post" action="<?= url('/videos/filter-presets/' . $preset['id'] . '/set-default') ?>" style="display:inline">
                        <button type="submit" class="btn-small"><?= e(t('videos.filter.set_default')) ?></button>
                    </form>
                <?php endif; ?>
                <form method="post" action="<?= url('/videos/filter-presets/' . $preset['id'] . '/delete') ?>"
                      onsubmit="return confirm('<?= e(t('videos.filter.delete_preset_confirm')) ?>');" style="display:inline">
                    <button type="submit" class="btn-small"><?= e(t('videos.filter.delete_preset')) ?></button>
                </form>
            </span>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<details <?= $hasActiveFilter ? 'open' : '' ?>>
    <summary><?= e(t('videos.filter.title')) ?></summary>
    <form method="get" action="<?= url('/videos') ?>">
        <p>
            <label for="q"><?= e(t('videos.filter.title_search_label')) ?></label><br>
            <input type="text" id="q" name="q" value="<?= e($titleQuery ?? '') ?>"
                   placeholder="<?= e(t('videos.filter.title_search_placeholder')) ?>" style="width: 100%; max-width: 400px;">
            <br><small><?= e(t('videos.filter.title_search_hint')) ?></small>
        </p>

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

        <p>
            <label for="video_type"><?= e(t('videos.filter.type_label')) ?></label><br>
            <select id="video_type" name="video_type">
                <option value=""><?= e(t('videos.filter.all_types')) ?></option>
                <?php foreach ($videoTypes as $typeValue): ?>
                    <option value="<?= e($typeValue) ?>" <?= $selectedType === $typeValue ? 'selected' : '' ?>>
                        <?= e(t('videos.type.' . $typeValue)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <?php foreach ($tagGroups as $group): ?>
            <fieldset>
                <legend><?= e(t('videos.tags_label')) ?> — <?= e($group['label']) ?></legend>
                <?php foreach ($group['tags'] as $tag): ?>
                    <label>
                        <input type="checkbox" name="tag_ids[]" value="<?= (int) $tag['id'] ?>"
                            <?= in_array((int) $tag['id'], $selectedTagIds, true) ? 'checked' : '' ?>>
                        <?= e($tag['name'] ?? $tag['slug']) ?>
                    </label>
                <?php endforeach; ?>
            </fieldset>
        <?php endforeach; ?>

        <button type="submit"><?= e(t('videos.filter.apply')) ?></button>
        <?php if ($hasActiveFilter): ?>
            <a href="<?= url('/videos') ?>"><?= e(t('videos.filter.reset')) ?></a>
        <?php endif; ?>
    </form>

    <?php if (\App\Core\Auth::check() && ($selectedArtistId !== null || $selectedType !== null || !empty($selectedTagIds))): ?>
        <form method="post" action="<?= url('/videos/filter-presets') ?>" style="margin-top: 1rem;">
            <input type="hidden" name="artist_id" value="<?= $selectedArtistId !== null ? (int) $selectedArtistId : '' ?>">
            <input type="hidden" name="video_type" value="<?= e($selectedType ?? '') ?>">
            <?php foreach ($selectedTagIds as $tagId): ?>
                <input type="hidden" name="tag_ids[]" value="<?= (int) $tagId ?>">
            <?php endforeach; ?>
            <label for="preset_name"><?= e(t('videos.filter.save_as')) ?></label><br>
            <input type="text" id="preset_name" name="name" placeholder="<?= e(t('videos.filter.preset_name_placeholder')) ?>" required>
            <label>
                <input type="checkbox" name="is_default" value="1">
                <?= e(t('videos.filter.make_default')) ?>
            </label>
            <button type="submit"><?= e(t('videos.filter.save_preset')) ?></button>
            <br><small><?= e(t('videos.filter.title_not_saved_hint')) ?></small>
        </form>
    <?php endif; ?>
</details>

<?php if (empty($videos)): ?>
    <p>
        <?php if ($hasActiveFilter): ?>
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

    <?php if ($totalPages > 1): ?>
        <?php
            $pageParams = [];
            if ($selectedArtistId !== null) {
                $pageParams['artist_id'] = $selectedArtistId;
            }
            if (!empty($selectedType)) {
                $pageParams['video_type'] = $selectedType;
            }
            if (!empty($titleQuery)) {
                $pageParams['q'] = $titleQuery;
            }
            $tagQuery = implode('', array_map(static fn ($tid) => '&tag_ids[]=' . (int) $tid, $selectedTagIds));
        ?>
        <style>
            .pagination { display: flex; align-items: center; gap: 1rem; margin: 1.5rem 0; }
        </style>
        <nav class="pagination">
            <?php if ($page > 1): ?>
                <a href="<?= url('/videos?' . http_build_query(array_merge($pageParams, ['page' => $page - 1])) . $tagQuery) ?>">
                    <?= e(t('pagination.previous')) ?>
                </a>
            <?php endif; ?>
            <span class="mono"><?= e(t('pagination.page_of')) ?> <?= (int) $page ?> / <?= (int) $totalPages ?></span>
            <?php if ($page < $totalPages): ?>
                <a href="<?= url('/videos?' . http_build_query(array_merge($pageParams, ['page' => $page + 1])) . $tagQuery) ?>">
                    <?= e(t('pagination.next')) ?>
                </a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
<?php endif; ?>
