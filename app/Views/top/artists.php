<?php
$fmt = static function ($n): string {
    return $n === null ? '—' : number_format((int) $n, 0, ',', ' ');
};

$trend = static function (array $row): string {
    if ($row['prev_rank'] === null) {
        return '<span class="top-trend top-new" title="' . e(t('top.new')) . '">★ ' . e(t('top.new')) . '</span>';
    }

    $delta = (int) $row['prev_rank'] - (int) $row['rank'];

    if ($delta > 0) {
        return '<span class="top-trend top-up" title="' . e(t('top.up')) . '">▲ +' . $delta . '</span>';
    }

    if ($delta < 0) {
        return '<span class="top-trend top-down" title="' . e(t('top.down')) . '">▼ ' . $delta . '</span>';
    }

    return '<span class="top-trend top-same" title="' . e(t('top.same')) . '">＝</span>';
};

$query = static function (array $extra) use ($maxSubscribers, $weekDate): string {
    $params = array_merge(['max' => $maxSubscribers, 'week' => $weekDate], $extra);

    return '?' . http_build_query(array_filter($params, static function ($v) { return $v !== null; }));
};
?>
<style>
    .top-list { list-style: none; margin: 0; padding: 0; }
    .top-row { display: flex; align-items: center; gap: 1rem; padding: 0.6rem 0; border-bottom: 1px solid rgba(128,128,128,.25); }
    .top-pos { font-size: 1.5rem; font-weight: 700; min-width: 2.2rem; text-align: right; font-variant-numeric: tabular-nums; }
    .top-avatar { width: 54px; height: 54px; flex: none; border-radius: 50%; background: rgba(128,128,128,.2) center / cover no-repeat; }
    .top-info { flex: 1; min-width: 0; }
    .top-info a { font-weight: 600; }
    .top-meta { display: block; font-size: .85rem; opacity: .75; }
    .top-trend { white-space: nowrap; font-weight: 700; min-width: 4.5rem; text-align: right; }
    .top-up { color: #1a7f37; }
    .top-down { color: #cf222e; }
    .top-same { color: #6e7781; }
    .top-new { color: #9a6700; }
    .top-nav { display: flex; justify-content: space-between; margin: .5rem 0 1.5rem; }
    .top-limits a, .top-limits strong { margin-right: .75rem; }
    @media (max-width: 600px) { .top-avatar { display: none; } .top-pos { font-size: 1.2rem; min-width: 1.6rem; } }
</style>

<h1><?= e(t('top.title')) ?></h1>

<p class="top-tabs">
    <a href="<?= url('/top') ?>"><?= e(t('top.tab_videos')) ?></a> ·
    <strong><?= e(t('top.tab_artists')) ?></strong>
</p>

<?php if ($weekDate === null): ?>
    <p><?= e(t('top.empty')) ?></p>
<?php else: ?>
    <p><small><?= e(t('top.artists_subtitle')) ?> — <?= e(t('top.week_of')) ?> <strong><?= e($weekDate) ?></strong></small></p>

    <p class="top-limits">
        <?= e(t('top.artists_limit')) ?>
        <?php foreach ($limits as $limit): ?>
            <?php if ($limit === $maxSubscribers): ?>
                <strong>&lt; <?= $fmt($limit) ?></strong>
            <?php else: ?>
                <a href="<?= url('/top/artists' . $query(['max' => $limit])) ?>">&lt; <?= $fmt($limit) ?></a>
            <?php endif; ?>
        <?php endforeach; ?>
    </p>

    <div class="top-nav">
        <span><?php if ($prevWeek !== null): ?><a href="<?= url('/top/artists' . $query(['week' => $prevWeek])) ?>">← <?= e(t('top.prev_week')) ?></a><?php endif; ?></span>
        <span><?php if ($nextWeek !== null): ?><a href="<?= url('/top/artists' . $query(['week' => $nextWeek])) ?>"><?= e(t('top.next_week')) ?> →</a><?php endif; ?></span>
    </div>

    <?php if (empty($artists)): ?>
        <p><?= e(t('top.artists_none')) ?></p>
    <?php else: ?>
        <ol class="top-list">
            <?php foreach ($artists as $a): ?>
                <li class="top-row">
                    <span class="top-pos"><?= (int) $a['rank'] ?></span>
                    <a href="<?= url('/artists/' . $a['slug']) ?>">
                        <span class="top-avatar"<?= !empty($a['avatar_path']) ? ' style="background-image:url(\'' . e($a['avatar_path']) . '\')"' : '' ?>></span>
                    </a>
                    <div class="top-info">
                        <a href="<?= url('/artists/' . $a['slug']) ?>"><?= e($a['name'] ?? '') ?></a>
                        <span class="top-meta">
                            +<?= $fmt($a['gain']) ?> <?= e(t('top.gain')) ?> · <?= $fmt($a['subscriber_count']) ?> <?= e(t('top.subscribers')) ?>
                        </span>
                        <?php if (!empty($a['best_video_id'])): ?>
                            <span class="top-meta">
                                ♪ <a href="<?= url('/videos/' . $a['best_video_id']) ?>"><?= e($a['best_video_title'] ?? '') ?></a>
                            </span>
                        <?php endif; ?>
                    </div>
                    <?= $trend($a) ?>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>
<?php endif; ?>
