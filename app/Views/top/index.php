<?php
/** @var ?string $weekDate */

$fmt = static function ($n): string {
    return $n === null ? '—' : number_format((int) $n, 0, ',', ' ');
};

$thumb = static function (?string $url): string {
    return $url ? ' style="background-image:url(\'' . e($url) . '\')"' : '';
};

// Icône d'évolution : rang de la semaine précédente vs rang actuel.
$trend = static function (array $row): string {
    if ($row['prev_rank'] === null) {
        return '<span class="top-trend top-new" title="' . e(t('top.new')) . '">★ ' . e(t('top.new')) . '</span>';
    }

    $delta = (int) $row['prev_rank'] - (int) $row['rank_pos'];

    if ($delta > 0) {
        return '<span class="top-trend top-up" title="' . e(t('top.up')) . '">▲ +' . $delta . '</span>';
    }

    if ($delta < 0) {
        return '<span class="top-trend top-down" title="' . e(t('top.down')) . '">▼ ' . $delta . '</span>';
    }

    return '<span class="top-trend top-same" title="' . e(t('top.same')) . '">＝</span>';
};
?>
<style>
    .top-list { list-style: none; margin: 0; padding: 0; }
    .top-row { display: flex; align-items: center; gap: 1rem; padding: 0.6rem 0; border-bottom: 1px solid rgba(128,128,128,.25); }
    .top-pos { font-size: 1.5rem; font-weight: 700; min-width: 2.2rem; text-align: right; font-variant-numeric: tabular-nums; }
    .top-thumb { width: 96px; height: 54px; flex: none; border-radius: 4px; background: rgba(128,128,128,.2) center / cover no-repeat; }
    .top-info { flex: 1; min-width: 0; }
    .top-info a { font-weight: 600; }
    .top-meta { display: block; font-size: .85rem; opacity: .75; }
    .top-trend { white-space: nowrap; font-weight: 700; min-width: 4.5rem; text-align: right; }
    .top-up { color: #1a7f37; }
    .top-down { color: #cf222e; }
    .top-same { color: #6e7781; }
    .top-new { color: #9a6700; }
    .top-nav { display: flex; justify-content: space-between; margin: .5rem 0 1.5rem; }
    @media (max-width: 600px) { .top-thumb { display: none; } .top-pos { font-size: 1.2rem; min-width: 1.6rem; } }
</style>

<h1><?= e(t('top.title')) ?></h1>

<?php if ($weekDate === null): ?>
    <p><?= e(t('top.empty')) ?></p>
    <?php if (\App\Core\Auth::check() && in_array(\App\Core\Auth::role(), ['moderator', 'admin'], true)): ?>
        <p><a href="<?= url('/admin/top') ?>"><?= e(t('top.admin_link')) ?></a></p>
    <?php endif; ?>
<?php else: ?>
    <p><small><?= e(t('top.subtitle')) ?> — <?= e(t('top.week_of')) ?> <strong><?= e($weekDate) ?></strong></small></p>

    <div class="top-nav">
        <span><?php if ($prevWeek !== null): ?><a href="<?= url('/top?week=' . $prevWeek) ?>">← <?= e(t('top.prev_week')) ?></a><?php endif; ?></span>
        <span><?php if ($nextWeek !== null): ?><a href="<?= url('/top?week=' . $nextWeek) ?>"><?= e(t('top.next_week')) ?> →</a><?php endif; ?></span>
    </div>

    <h2><?= e(t('top.top10')) ?></h2>
    <?php if (empty($top)): ?>
        <p><?= e(t('top.none')) ?></p>
    <?php else: ?>
        <ol class="top-list">
            <?php foreach ($top as $i => $row): ?>
                <li class="top-row">
                    <span class="top-pos"><?= $i + 1 ?></span>
                    <a href="<?= url('/videos/' . $row['id']) ?>"><span class="top-thumb"<?= $thumb($row['thumbnail_url'] ?? null) ?>></span></a>
                    <div class="top-info">
                        <a href="<?= url('/videos/' . $row['id']) ?>"><?= e($row['title'] ?? $row['youtube_id']) ?></a>
                        <span class="top-meta">
                            <?= e($row['artist_names'] ?? '') ?>
                        </span>
                        <span class="top-meta">
                            +<?= $fmt($row['gain']) ?> <?= e(t('top.gain')) ?> · <?= $fmt($row['views']) ?> <?= e(t('top.views')) ?>
                        </span>
                    </div>
                    <?= $trend($row) ?>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>

    <h2><?= e(t('top.best_progress')) ?></h2>
    <?php if (empty($bestProgress)): ?>
        <p><?= e(t('top.no_progress')) ?></p>
    <?php else: ?>
        <ol class="top-list">
            <?php foreach ($bestProgress as $row): ?>
                <li class="top-row">
                    <span class="top-pos">#<?= (int) $row['rank_pos'] ?></span>
                    <a href="<?= url('/videos/' . $row['id']) ?>"><span class="top-thumb"<?= $thumb($row['thumbnail_url'] ?? null) ?>></span></a>
                    <div class="top-info">
                        <a href="<?= url('/videos/' . $row['id']) ?>"><?= e($row['title'] ?? $row['youtube_id']) ?></a>
                        <span class="top-meta"><?= e($row['artist_names'] ?? '') ?></span>
                        <span class="top-meta">#<?= (int) $row['prev_rank'] ?> → #<?= (int) $row['rank_pos'] ?> · +<?= $fmt($row['gain']) ?> <?= e(t('top.gain')) ?></span>
                    </div>
                    <?= $trend($row) ?>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>

    <h2><?= e(t('top.releases')) ?></h2>
    <?php if (empty($releases)): ?>
        <p><?= e(t('top.no_releases')) ?></p>
    <?php else: ?>
        <ul class="top-list">
            <?php foreach ($releases as $row): ?>
                <li class="top-row">
                    <a href="<?= url('/videos/' . $row['id']) ?>"><span class="top-thumb"<?= $thumb($row['thumbnail_url'] ?? null) ?>></span></a>
                    <div class="top-info">
                        <a href="<?= url('/videos/' . $row['id']) ?>"><?= e($row['title'] ?? $row['youtube_id']) ?></a>
                        <span class="top-meta"><?= e($row['artist_names'] ?? '') ?> — <?= e($row['release_date']) ?></span>
                    </div>
                    <span class="top-meta"><?= $fmt($row['views']) ?> <?= e(t('top.views')) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
<?php endif; ?>
