<style>
    .home-hero {
        padding: 2rem 0 2.5rem;
        border-bottom: 1px solid var(--line, #2a2c4a);
        margin-bottom: 2rem;
    }
    .home-hero p {
        max-width: 60ch;
        color: var(--ink-dim, #9a9cc0);
    }
    .home-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1.25rem;
    }
    .home-section {
        margin-bottom: 3rem;
    }
    .home-section-head {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }
    .home-section-head h2 {
        margin: 0;
    }
    .home-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 1rem;
    }
    .home-grid.is-compact {
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    }
    .home-card-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        margin-top: 0.35rem;
    }
    .home-genre-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 0.75rem;
    }
    .home-genre-chip {
        display: block;
        text-align: center;
        padding: 0.9rem 0.75rem;
        border: 1px solid var(--line, #2a2c4a);
        border-radius: 6px;
        text-decoration: none;
        color: inherit;
        font-weight: 500;
        transition: border-color 0.15s ease, background 0.15s ease;
    }
    .home-genre-chip:hover {
        border-color: var(--mint, #38d6b4);
        background: rgba(56, 214, 180, 0.06);
    }
    .home-playlist-cover {
        display: grid;
        grid-template-columns: 1fr 1fr;
        grid-template-rows: 1fr 1fr;
        gap: 2px;
        aspect-ratio: 1 / 1;
        overflow: hidden;
        border-radius: 6px;
        background: var(--navy-900, #191b3a);
    }
    .home-playlist-cover.has-1 {
        grid-template-columns: 1fr;
        grid-template-rows: 1fr;
    }
    .home-playlist-cover.has-2 {
        grid-template-columns: 1fr;
        grid-template-rows: 1fr 1fr;
    }
    .home-playlist-cover.has-3 > :last-child {
        grid-column: span 2;
    }
    .home-playlist-cover div {
        background-size: cover;
        background-position: center;
    }
    .home-stats {
        text-align: center;
        color: var(--ink-dim, #9a9cc0);
        font-size: 0.9rem;
        padding: 1rem 0;
        border-top: 1px solid var(--line, #2a2c4a);
    }
    @media (max-width: 480px) {
        .home-grid, .home-genre-grid {
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        }
    }
</style>

<?php
$placeholderThumb = static function (?string $url): string {
    return $url
        ? ' style="background-image:url(\'' . e($url) . '\')"'
        : '';
};
?>

<section class="home-hero">
    <h1><?= e(t('home.hero_title')) ?></h1>
    <p><?= e(t('home.hero_subtitle')) ?></p>
    <div class="home-hero-actions">
        <a href="<?= url('/videos') ?>" class="btn"><?= e(t('home.explore_videos')) ?></a>
        <a href="<?= url('/artists') ?>" class="btn btn-small"><?= e(t('home.discover_artists_cta')) ?></a>
    </div>
</section>

<?php if (!empty($latestVideos)): ?>
<section class="home-section" aria-labelledby="home-latest-heading">
    <div class="home-section-head">
        <h2 id="home-latest-heading"><?= e(t('home.latest_releases')) ?></h2>
        <a href="<?= url('/videos') ?>"><?= e(t('home.view_all_videos')) ?></a>
    </div>
    <ul class="home-grid">
        <?php foreach ($latestVideos as $video): ?>
            <li class="card">
                <a href="<?= url('/videos/' . $video['id']) ?>">
                    <div class="card-thumb"<?= $placeholderThumb($video['thumbnail_url'] ?? null) ?>></div>
                </a>
                <div class="card-body">
                    <a href="<?= url('/videos/' . $video['id']) ?>" class="card-title">
                        <?= e($video['title'] ?? $video['youtube_id']) ?>
                    </a>
                    <span class="card-meta">
                        <?php if (!empty($video['artist_names'])): ?><?= e($video['artist_names']) ?><?php endif; ?>
                        <?php if (!empty($video['release_date'])): ?> — <?= e($video['release_date']) ?><?php endif; ?>
                    </span>
                    <?php if (!empty($video['tags'])): ?>
                        <div class="home-card-tags">
                            <?php foreach ($video['tags'] as $tagName): ?>
                                <span class="tag"><?= e($tagName) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
<?php endif; ?>

<?php if (!empty($discoverVideos)): ?>
<section class="home-section" aria-labelledby="home-discover-heading">
    <div class="home-section-head">
        <h2 id="home-discover-heading"><?= e(t('home.discover')) ?></h2>
    </div>
    <ul class="home-grid">
        <?php foreach ($discoverVideos as $video): ?>
            <li class="card">
                <a href="<?= url('/videos/' . $video['id']) ?>">
                    <div class="card-thumb"<?= $placeholderThumb($video['thumbnail_url'] ?? null) ?>></div>
                </a>
                <div class="card-body">
                    <a href="<?= url('/videos/' . $video['id']) ?>" class="card-title">
                        <?= e($video['title'] ?? $video['youtube_id']) ?>
                    </a>
                    <span class="card-meta">
                        <?php if (!empty($video['artist_names'])): ?><?= e($video['artist_names']) ?><?php endif; ?>
                    </span>
                    <?php if (!empty($video['tags'])): ?>
                        <div class="home-card-tags">
                            <?php foreach ($video['tags'] as $tagName): ?>
                                <span class="tag"><?= e($tagName) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
<?php endif; ?>

<?php if (!empty($discoverArtists)): ?>
<section class="home-section" aria-labelledby="home-discover-artists-heading">
    <div class="home-section-head">
        <h2 id="home-discover-artists-heading"><?= e(t('home.discover_artists')) ?></h2>
        <a href="<?= url('/artists') ?>"><?= e(t('home.view_all_artists')) ?></a>
    </div>
    <ul class="home-grid is-compact">
        <?php foreach ($discoverArtists as $artist): ?>
            <li class="card">
                <a href="<?= url('/artists/' . $artist['slug']) ?>">
                    <div class="card-thumb" style="aspect-ratio: 1 / 1;<?= !empty($artist['avatar_path']) ? " background-image:url('" . e($artist['avatar_path']) . "')" : '' ?>"></div>
                </a>
                <div class="card-body">
                    <a href="<?= url('/artists/' . $artist['slug']) ?>" class="card-title">
                        <?= e($artist['name'] ?? $artist['slug']) ?>
                    </a>
                    <span class="card-meta">
                        <?= e(t('artists.type.' . $artist['type'])) ?>
                        — <?= (int) $artist['video_count'] ?> <?= e(t('footer.videos_count')) ?>
                    </span>
                    <?php if (!empty($artist['tags'])): ?>
                        <div class="home-card-tags">
                            <?php foreach ($artist['tags'] as $tagName): ?>
                                <span class="tag"><?= e($tagName) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
<?php endif; ?>

<?php if (!empty($genreTags)): ?>
<section class="home-section" aria-labelledby="home-genres-heading">
    <div class="home-section-head">
        <h2 id="home-genres-heading"><?= e(t('home.explore_genres')) ?></h2>
    </div>
    <div class="home-genre-grid">
        <?php foreach ($genreTags as $tag): ?>
            <a class="home-genre-chip" href="<?= url('/videos?' . http_build_query(['tag_ids' => [(int) $tag['id']]])) ?>">
                <?= e($tag['name'] ?? $tag['slug']) ?>
            </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($playlists)): ?>
<section class="home-section" aria-labelledby="home-playlists-heading">
    <div class="home-section-head">
        <h2 id="home-playlists-heading"><?= e(t('home.playlists')) ?></h2>
        <a href="<?= url('/playlists') ?>"><?= e(t('home.view_all_playlists')) ?></a>
    </div>
    <ul class="home-grid">
        <?php foreach ($playlists as $playlist): ?>
            <?php $thumbs = $playlist['preview_thumbnails'] ?? []; ?>
            <li class="card">
                <a href="<?= url('/playlists/' . $playlist['id']) ?>">
                    <div class="home-playlist-cover has-<?= max(1, count($thumbs)) ?>">
                        <?php if (empty($thumbs)): ?>
                            <div></div>
                        <?php else: ?>
                            <?php foreach ($thumbs as $thumbUrl): ?>
                                <div style="background-image:url('<?= e($thumbUrl) ?>')"></div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </a>
                <div class="card-body">
                    <a href="<?= url('/playlists/' . $playlist['id']) ?>" class="card-title">
                        <?= e($playlist['name']) ?>
                    </a>
                    <?php if (!empty($playlist['description'])): ?>
                        <p><?= e(mb_strimwidth($playlist['description'], 0, 90, '…')) ?></p>
                    <?php endif; ?>
                    <span class="card-meta">
                        <?= (int) ($playlist['video_count'] ?? 0) ?> <?= e(t('playlists.video_count')) ?>
                        <?php if (!empty($playlist['owner_name'])): ?>
                            — <?= e(t('playlists.by')) ?> <?= e($playlist['owner_name']) ?>
                        <?php endif; ?>
                    </span>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
<?php endif; ?>

<?php if (!empty($newArtists)): ?>
<section class="home-section" aria-labelledby="home-new-artists-heading">
    <div class="home-section-head">
        <h2 id="home-new-artists-heading"><?= e(t('home.new_artists')) ?></h2>
    </div>
    <ul class="home-grid is-compact">
        <?php foreach ($newArtists as $artist): ?>
            <li class="card">
                <a href="<?= url('/artists/' . $artist['slug']) ?>">
                    <div class="card-thumb" style="aspect-ratio: 1 / 1;<?= !empty($artist['avatar_path']) ? " background-image:url('" . e($artist['avatar_path']) . "')" : '' ?>"></div>
                </a>
                <div class="card-body">
                    <a href="<?= url('/artists/' . $artist['slug']) ?>" class="card-title">
                        <?= e($artist['name'] ?? $artist['slug']) ?>
                    </a>
                    <span class="card-meta">
                        <?= e(t('artists.type.' . $artist['type'])) ?>
                        — <?= (int) $artist['video_count'] ?> <?= e(t('footer.videos_count')) ?>
                    </span>
                    <?php if (!empty($artist['tags'])): ?>
                        <div class="home-card-tags">
                            <?php foreach ($artist['tags'] as $tagName): ?>
                                <span class="tag"><?= e($tagName) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
<?php endif; ?>

<p class="home-stats">
    <?= (int) $artistCount ?> <?= e(t('footer.artists_count')) ?>
    &nbsp;·&nbsp;
    <?= (int) $videoCount ?> <?= e(t('footer.videos_count')) ?>
    &nbsp;·&nbsp;
    <?= (int) $playlistCount ?> <?= e(t('home.playlists')) ?>
</p>
