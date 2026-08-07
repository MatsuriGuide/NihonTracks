<h1><?= e(t('videos.url_title')) ?></h1>

<?php if (!empty($error)): ?>
    <p class="errors"><?= e($error) ?></p>
<?php endif; ?>

<form method="post" action="<?= url('/videos/preview') ?>">
    <p>
        <label for="youtube_url"><?= e(t('videos.url_label')) ?></label><br>
        <input type="url" id="youtube_url" name="youtube_url"
               placeholder="https://www.youtube.com/watch?v=..." required
               style="width: 100%; max-width: 500px;">
    </p>
    <button type="submit"><?= e(t('videos.url_submit')) ?></button>
</form>
