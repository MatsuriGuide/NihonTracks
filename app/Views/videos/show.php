<h1><?= e($translation['title'] ?? $video['youtube_id']) ?></h1>

<p>
    <iframe width="560" height="315"
        src="https://www.youtube.com/embed/<?= e($video['youtube_id']) ?>"
        title="<?= e($translation['title'] ?? '') ?>" frameborder="0" allowfullscreen></iframe>
</p>

<p>
    Type : <?= e($video['video_type']) ?>
    <?php if (!empty($video['release_date'])): ?>
        — Sortie le <?= e($video['release_date']) ?>
    <?php endif; ?>
    <?php if (!empty($video['channel_name'])): ?>
        — Chaîne : <?= e($video['channel_name']) ?>
    <?php endif; ?>
</p>

<?php if (!empty($artists)): ?>
    <p>
        Artiste(s) :
        <?php foreach ($artists as $i => $artist): ?>
            <?= $i > 0 ? ', ' : '' ?><?= e($artist['name'] ?? '') ?>
        <?php endforeach; ?>
    </p>
<?php endif; ?>

<?php if (!empty($tags)): ?>
    <p>
        Tags :
        <?php foreach ($tags as $tag): ?>
            <span class="tag"><?= e($tag['name'] ?? '') ?></span>
        <?php endforeach; ?>
    </p>
<?php endif; ?>

<?php if (\App\Core\Auth::canEdit((int) $video['added_by'])): ?>
    <p>
        <a href="<?= url('/videos/' . $video['id'] . '/edit') ?>">Modifier</a>
        &nbsp;
        <form method="post" action="<?= url('/videos/' . $video['id'] . '/delete') ?>"
              onsubmit="return confirm('Supprimer cette vidéo ?');" style="display:inline">
            <button type="submit">Supprimer</button>
        </form>
    </p>
<?php endif; ?>

<p><a href="<?= url('/videos') ?>">&larr; Retour à la liste</a></p>
