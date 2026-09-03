<h1><?= $mode === 'edit' ? e(t('artists.edit_title')) : e(t('artists.create_title')) ?></h1>

<?php if (!empty($errors)): ?>
    <ul class="errors">
        <?php foreach ($errors as $error): ?>
            <li><?= e($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if ($mode === 'edit' && \App\Core\Auth::role() === 'admin'): ?>
    <p>
        <button type="button" id="suggest-artist-info-btn"
                data-url="<?= url('/artists/' . $artistId . '/suggest-info') ?>"
                data-loading-label="<?= e(t('artists.suggest_info_loading')) ?>">
            <?= e(t('artists.suggest_info')) ?>
        </button>
    </p>
    <p><small><?= e(t('artists.suggest_info_hint')) ?></small></p>

    <details>
        <summary><?= e(t('artists.json_fill_toggle')) ?></summary>
        <p>
            <label for="json_fill_input"><?= e(t('artists.json_fill_label')) ?></label><br>
            <textarea id="json_fill_input" rows="6"
                      placeholder='{"start_year": 2015, "end_year": null, "label": "...", "bio": "...", "tags": ["J-Pop", "Chanteuse"]}'
                      style="width: 100%; max-width: 500px;"></textarea>
        </p>
        <p><small><?= e(t('artists.json_fill_hint')) ?></small></p>
        <p>
            <button type="button" id="json-fill-btn"
                    data-tags-url="<?= url('/artists/' . $artistId . '/tags-by-name') ?>"
                    data-invalid-label="<?= e(t('artists.json_fill_invalid')) ?>"
                    data-success-label="<?= e(t('artists.json_fill_success')) ?>"
                    data-empty-label="<?= e(t('artists.json_fill_empty')) ?>"
                    data-tags-applied-label="<?= e(t('artists.json_fill_tags_applied')) ?>"
                    data-tags-unmatched-label="<?= e(t('artists.json_fill_tags_unmatched')) ?>">
                <?= e(t('artists.json_fill_submit')) ?>
            </button>
            <span id="json-fill-status"></span>
        </p>
    </details>
<?php endif; ?>

<form method="post" action="<?= $mode === 'edit' ? url('/artists/' . $artistId . '/edit') : url('/artists/create') ?>">
    <p>
        <label for="name"><?= e(t('artists.name')) ?></label><br>
        <input type="text" id="name" name="name" value="<?= e($old['name'] ?? '') ?>" required>
    </p>

    <p>
        <label for="type"><?= e(t('artists.type')) ?></label><br>
        <select id="type" name="type">
            <?php foreach (['solo', 'group', 'duo', 'other'] as $value): ?>
                <option value="<?= e($value) ?>" <?= ($old['type'] ?? 'solo') === $value ? 'selected' : '' ?>>
                    <?= e(t('artists.type.' . $value)) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <label for="status"><?= e(t('artists.status')) ?></label><br>
        <select id="status" name="status">
            <?php foreach (['active', 'disbanded', 'hiatus'] as $value): ?>
                <option value="<?= e($value) ?>" <?= ($old['status'] ?? 'active') === $value ? 'selected' : '' ?>>
                    <?= e(t('artists.status.' . $value)) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <label for="start_year"><?= e(t('artists.start_year')) ?></label><br>
        <input type="number" id="start_year" name="start_year" value="<?= e((string) ($old['start_year'] ?? '')) ?>">
    </p>

    <p>
        <label for="end_year"><?= e(t('artists.end_year')) ?></label><br>
        <input type="number" id="end_year" name="end_year" value="<?= e((string) ($old['end_year'] ?? '')) ?>">
    </p>

    <p>
        <label for="label"><?= e(t('artists.label')) ?></label><br>
        <input type="text" id="label" name="label" value="<?= e($old['label'] ?? '') ?>">
    </p>

    <p>
        <label for="bio"><?= e(t('artists.bio')) ?></label><br>
        <textarea id="bio" name="bio" rows="6"><?= e($old['bio'] ?? '') ?></textarea>
    </p>

    <?php if ($mode === 'edit'): ?>
        <fieldset id="artist-tags-fieldset">
            <legend><?= e(t('artists.tags_label')) ?></legend>
            <?php foreach ($tagGroups as $group): ?>
                <fieldset>
                    <legend><?= e($group['label']) ?></legend>
                    <?php foreach ($group['tags'] as $tag): ?>
                        <label>
                            <input type="checkbox" name="tag_ids[]" value="<?= (int) $tag['id'] ?>"
                                   data-tag-name="<?= e(mb_strtolower($tag['name'] ?? $tag['slug'])) ?>"
                                <?= in_array((int) $tag['id'], $selectedTagIds, true) ? 'checked' : '' ?>>
                            <?= e($tag['name'] ?? $tag['slug']) ?>
                        </label><br>
                    <?php endforeach; ?>
                </fieldset>
            <?php endforeach; ?>
        </fieldset>
    <?php endif; ?>

    <button type="submit"><?= $mode === 'edit' ? e(t('artists.submit_edit')) : e(t('artists.submit_create')) ?></button>
</form>

<?php if ($mode === 'edit' && \App\Core\Auth::role() === 'admin'): ?>
    <script src="<?= asset('js/artist-info-suggest.js') ?>"></script>
    <script src="<?= asset('js/artist-json-fill.js') ?>"></script>
<?php endif; ?>
