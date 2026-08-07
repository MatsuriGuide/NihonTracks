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

<?php if (\App\Core\Auth::check()): ?>
    <h2>Ajouter à une playlist</h2>
    <?php if (empty($userPlaylists)): ?>
        <p>Tu n'as pas encore de playlist. <a href="<?= url('/playlists/create') ?>">En créer une</a>.</p>
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
        <a href="<?= url('/videos/' . $video['id'] . '/edit') ?>">Modifier</a>
        &nbsp;
        <form method="post" action="<?= url('/videos/' . $video['id'] . '/delete') ?>"
              onsubmit="return confirm('Supprimer cette vidéo ?');" style="display:inline">
            <button type="submit">Supprimer</button>
        </form>
    </p>
<?php endif; ?>

<?php if (\App\Core\Auth::check()): ?>
    <details>
        <summary>Signaler cette vidéo</summary>
        <form method="post" action="<?= url('/reports') ?>">
            <input type="hidden" name="reportable_type" value="video">
            <input type="hidden" name="reportable_id" value="<?= (int) $video['id'] ?>">
            <p>
                <label for="reason">Motif</label><br>
                <select id="reason" name="reason">
                    <option value="duplicate">Doublon</option>
                    <option value="wrong_info">Information erronée</option>
                    <option value="spam">Spam</option>
                    <option value="inappropriate">Contenu inapproprié</option>
                    <option value="other">Autre</option>
                </select>
            </p>
            <p>
                <label for="comment">Commentaire (optionnel)</label><br>
                <textarea id="comment" name="comment" rows="3"></textarea>
            </p>
            <button type="submit">Envoyer le signalement</button>
        </form>
    </details>
<?php endif; ?>

<p><a href="<?= url('/videos') ?>">&larr; Retour à la liste</a></p>
