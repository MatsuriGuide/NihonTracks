<h1><?= e($translation['name'] ?? $artist['slug']) ?></h1>

<p>
    Type : <?= e($artist['type']) ?> —
    Statut : <?= e($artist['status']) ?>
    <?php if (!empty($artist['start_year'])): ?>
        — Depuis <?= e((string) $artist['start_year']) ?><?= !empty($artist['end_year']) ? ' jusqu\'à ' . e((string) $artist['end_year']) : '' ?>
    <?php endif; ?>
</p>

<?php if (!empty($artist['label'])): ?>
    <p>Label : <?= e($artist['label']) ?></p>
<?php endif; ?>

<?php if (!empty($translation['bio'])): ?>
    <p><?= nl2br(e($translation['bio'])) ?></p>
<?php endif; ?>

<?php if (\App\Core\Auth::canEdit((int) $artist['created_by'])): ?>
    <p>
        <a href="<?= url('/artists/' . $artist['id'] . '/edit') ?>">Modifier</a>
        &nbsp;
        <form method="post" action="<?= url('/artists/' . $artist['id'] . '/delete') ?>"
              onsubmit="return confirm('Supprimer cet artiste ?');" style="display:inline">
            <button type="submit">Supprimer</button>
        </form>
    </p>
<?php endif; ?>

<p><a href="<?= url('/artists') ?>">&larr; Retour à la liste</a></p>
