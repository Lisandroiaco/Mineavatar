<?php

declare(strict_types=1);

require __DIR__ . '/includes/data.php';
require __DIR__ . '/includes/layout.php';
require __DIR__ . '/includes/repository.php';

$page = isset($_GET['page']) ? (string) $_GET['page'] : 'home';
$query = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
$filterTag = isset($_GET['tag']) ? trim((string) $_GET['tag']) : '';
$skinSlug = isset($_GET['skin']) ? (string) $_GET['skin'] : '';

$databaseError = null;
$loadError = null;
$searchError = null;
$persistError = null;
$searchNotice = null;

$pdo = connect_database($databaseError);
$skins = load_skins($skins, $pdo, $loadError);

$liveSearchSkin = null;
if ($query !== '') {
    $liveSearchSkin = fetch_mojang_skin($query, $searchError);
    if ($liveSearchSkin !== null) {
        $existingIndex = null;
        foreach ($skins as $index => $skin) {
            if (($skin['texture_hash'] ?? '') === ($liveSearchSkin['texture_hash'] ?? '') || $skin['uuid'] === $liveSearchSkin['uuid']) {
                $existingIndex = $index;
                break;
            }
        }

        if ($existingIndex !== null) {
            $skins[$existingIndex]['profiles'] = array_values(array_unique(array_merge(
                $skins[$existingIndex]['profiles'],
                $liveSearchSkin['profiles']
            )));
            $skins[$existingIndex]['tags'] = array_values(array_unique(array_merge(
                $skins[$existingIndex]['tags'],
                $liveSearchSkin['tags']
            )));
            $skins[$existingIndex]['views'] = max((int) $skins[$existingIndex]['views'], (int) $liveSearchSkin['views']);
            $skins[$existingIndex]['accent'] = $liveSearchSkin['accent'];
            $skins[$existingIndex]['texture_hash'] = $liveSearchSkin['texture_hash'] ?? ($skins[$existingIndex]['texture_hash'] ?? '');
            $liveSearchSkin = $skins[$existingIndex];
        } else {
            array_unshift($skins, $liveSearchSkin);
        }

        if ($pdo !== null) {
            persist_search_result($pdo, $liveSearchSkin, $persistError);
        }

        $searchNotice = 'Live Mojang search imported for "' . $liveSearchSkin['owner'] . '".';
    }
}

$filteredSkins = array_values(array_filter($skins, static function (array $skin) use ($query, $filterTag): bool {
    $haystack = strtolower(implode(' ', [
        $skin['name'],
        $skin['owner'],
        $skin['uuid'],
        implode(' ', $skin['tags']),
        implode(' ', $skin['profiles']),
    ]));

    $matchesQuery = $query === '' || str_contains($haystack, strtolower($query));
    $matchesTag = $filterTag === '' || in_array($filterTag, $skin['tags'], true);

    return $matchesQuery && $matchesTag;
}));

$selectedSkin = null;
if ($skinSlug !== '') {
    foreach ($skins as $skin) {
        if ($skin['slug'] === $skinSlug) {
            $selectedSkin = $skin;
            break;
        }
    }
}

$totalProfiles = array_sum(array_map(static fn(array $skin): int => count($skin['profiles']), $skins));
$totalViews = array_sum(array_map(static fn(array $skin): int => (int) $skin['views'], $skins));
$spotlightSkin = $liveSearchSkin ?? ($skins[0] ?? null);
$catalogHeadline = $page === 'skins' ? 'Skin catalog' : 'Featured skins';
$relatedSkins = [];

if ($selectedSkin !== null) {
    $relatedSkins = array_values(array_filter($skins, static fn(array $skin): bool => $skin['slug'] !== $selectedSkin['slug']));
    $relatedSkins = array_slice($relatedSkins, 0, 6);
}

render_header($page === 'home' ? 'skins' : $page, 'SkinForge');
?>

<?php if ($searchNotice !== null || $searchError !== null || $databaseError !== null || $loadError !== null || $persistError !== null): ?>
    <section class="notice-stack reveal">
        <?php if ($searchNotice !== null): ?>
            <div class="notice notice--success"><?= e($searchNotice) ?></div>
        <?php endif; ?>
        <?php if ($searchError !== null): ?>
            <div class="notice notice--warning"><?= e($searchError) ?></div>
        <?php endif; ?>
        <?php if ($databaseError !== null): ?>
            <div class="notice notice--info">MySQL mode is inactive until credentials are configured. Detail: <?= e($databaseError) ?></div>
        <?php elseif ($pdo === null): ?>
            <div class="notice notice--info">SkinForge is running in local mode. Add your MySQL credentials when you want search history and live results to persist.</div>
        <?php endif; ?>
        <?php if ($loadError !== null): ?>
            <div class="notice notice--warning">Database read fallback used: <?= e($loadError) ?></div>
        <?php endif; ?>
        <?php if ($persistError !== null): ?>
            <div class="notice notice--warning">Live result could not be persisted: <?= e($persistError) ?></div>
        <?php endif; ?>
    </section>
<?php endif; ?>

<?php if ($page === 'skin' && $selectedSkin !== null): ?>
    <?php
    $selectedTextureHash = (string) ($selectedSkin['texture_hash'] ?? '');
    $selectedTextureUrl = skin_texture_url($selectedSkin['owner'], $selectedSkin['uuid'], $selectedTextureHash, 512);
    $shortUuid = substr($selectedSkin['uuid'], 0, 8) . '...';
    $shortTexture = $selectedTextureHash !== '' ? substr($selectedTextureHash, 0, 14) . '...' : 'Live lookup';
    $authNext = rawurlencode('index.php?page=skin&skin=' . $selectedSkin['slug']);
    ?>
    <section class="hero hero--detail reveal">
        <div class="detail-view">
            <div class="detail-view__viewer card">
                <div class="panel-top">
                    <span class="panel-arrow">←</span>
                    <span>Interactive Minecraft 3D viewer</span>
                    <span class="panel-arrow">→</span>
                </div>
                <div
                    class="skin-stage image-shell image-shell--large js-stage js-skin-viewer"
                    data-skin-url="<?= e($selectedTextureUrl) ?>"
                    data-variant="<?= e(strtolower($selectedSkin['variant'])) ?>"
                    data-name="<?= e($selectedSkin['name']) ?>"
                >
                    <div class="cinema-bars" aria-hidden="true"></div>
                    <div class="viewer-hud">
                        <span class="viewer-hud__chip js-viewer-mode">IDLE</span>
                        <span class="viewer-hud__chip js-viewer-camera">FRONT CAM</span>
                    </div>
                    <div class="skin-stage__glow" style="--accent: <?= e($selectedSkin['accent']) ?>"></div>
                    <canvas class="skin-stage__canvas js-skin-canvas" width="520" height="520" aria-label="<?= e($selectedSkin['name']) ?> 3D viewer"></canvas>
                    <img
                        class="skin-stage__render js-render-fallback"
                        src="<?= e(assembled_skin_url($selectedSkin['uuid'], $selectedTextureHash, $selectedSkin['variant'], 512, 'frontfull')) ?>"
                        alt="<?= e($selectedSkin['name']) ?> official Minecraft render"
                        hidden
                    >
                </div>
                <div class="render-toolbar">
                    <div class="render-toolbar__group">
                        <button class="render-control viewer-control is-active" type="button" data-viewer-display="3d">3D</button>
                        <button class="render-control viewer-control" type="button" data-viewer-display="2d">2D</button>
                    </div>
                    <div class="render-toolbar__group">
                        <button class="render-control viewer-control is-active" type="button" data-viewer-animation="idle">Idle</button>
                        <button class="render-control viewer-control" type="button" data-viewer-animation="walk">Walk</button>
                        <button class="render-control viewer-control" type="button" data-viewer-animation="run">Run</button>
                        <button class="render-control viewer-control" type="button" data-viewer-animation="fly">Fly</button>
                    </div>
                    <div class="render-toolbar__group">
                        <button class="render-control viewer-control" type="button" data-viewer-pan="front">Front</button>
                        <button class="render-control viewer-control" type="button" data-viewer-pan="back">Back</button>
                        <button class="render-control viewer-control" type="button" data-viewer-pan="left">Left</button>
                        <button class="render-control viewer-control" type="button" data-viewer-pan="right">Right</button>
                        <button class="render-control viewer-control" type="button" data-viewer-cinema="true">Cinema</button>
                        <button class="render-control viewer-control viewer-control--accent" type="button" data-viewer-cycle="true">Play all</button>
                    </div>
                </div>
                <div class="detail-stat detail-stat--triple">
                    <div>
                        <strong><?= e((string) $selectedSkin['views']) ?></strong>
                        <span>Total views</span>
                    </div>
                    <div>
                        <strong><?= e((string) $selectedSkin['likes']) ?></strong>
                        <span>Likes saved</span>
                    </div>
                    <div>
                        <strong><?= e((string) count($selectedSkin['profiles'])) ?></strong>
                        <span>Linked profiles</span>
                    </div>
                </div>
                <p class="viewer-note">Rotate the official skin in real 3D, launch idle or movement loops, and switch to front-facing camera moments instantly.</p>
            </div>

            <div class="detail-view__meta">
                <div class="card meta-card">
                    <div class="eyebrow">SkinForge profile</div>
                    <h1><?= e($selectedSkin['name']) ?> minecraft skin</h1>
                    <p><?= e($selectedSkin['description']) ?></p>
                    <div class="detail-chip-row">
                        <span class="detail-chip">Official texture</span>
                        <span class="detail-chip"><?= e($selectedSkin['variant']) ?> model</span>
                        <span class="detail-chip">Mojang linked</span>
                    </div>
                    <div class="tag-row">
                        <?php foreach ($selectedSkin['tags'] as $tag): ?>
                            <a class="tag-chip" href="index.php?page=skins&tag=<?= rawurlencode($tag) ?>"><?= e($tag) ?></a>
                        <?php endforeach; ?>
                    </div>
                    <div class="stats-grid">
                        <div><strong><?= e((string) $selectedSkin['views']) ?></strong><span>Views</span></div>
                        <div><strong><?= e((string) $selectedSkin['likes']) ?></strong><span>Likes</span></div>
                        <div><strong><?= e((string) count($selectedSkin['profiles'])) ?></strong><span>Profiles</span></div>
                    </div>
                </div>

                <div class="card">
                    <div class="card__title-row">
                        <h2>Export and share</h2>
                        <span class="badge-soft">Studio</span>
                    </div>
                    <p class="card-copy">Premium actions like download and likes now push visitors into account creation, while sharing remains instant.</p>
                    <div class="quick-tools">
                        <a class="tool-card tool-card--locked js-auth-gate" href="register.php?gate=download&next=<?= $authNext ?>" data-auth-action="download">Download texture</a>
                        <a class="tool-card tool-card--locked js-auth-gate" href="register.php?gate=render&next=<?= $authNext ?>" data-auth-action="render">Save front render</a>
                        <button class="tool-card tool-card--button js-copy-uuid" type="button" data-copy-value="<?= e($selectedSkin['uuid']) ?>">Copy UUID</button>
                        <button class="tool-card tool-card--button js-copy-url" type="button">Copy share link</button>
                    </div>
                </div>

                <div class="card">
                    <div class="card__title-row">
                        <h2>Skin intelligence</h2>
                        <span class="badge-soft"><?= e($selectedSkin['name']) ?></span>
                    </div>
                    <div class="info-list">
                        <div class="info-list__row"><span>UUID</span><strong><?= e($shortUuid) ?></strong></div>
                        <div class="info-list__row"><span>Texture hash</span><strong><?= e($shortTexture) ?></strong></div>
                        <div class="info-list__row"><span>Source</span><strong>Mojang</strong></div>
                        <div class="info-list__row"><span>Variant</span><strong><?= e($selectedSkin['variant']) ?></strong></div>
                        <div class="info-list__row"><span>Owner</span><strong><?= e($selectedSkin['owner']) ?></strong></div>
                    </div>
                </div>

                <div class="card">
                    <div class="card__title-row">
                        <h2>Profiles wearing this skin</h2>
                        <span class="badge-soft"><?= e((string) count($selectedSkin['profiles'])) ?> linked</span>
                    </div>
                    <div class="profile-list">
                        <?php foreach ($selectedSkin['profiles'] as $profileName): ?>
                            <a class="profile-list__item profile-list__item--link js-profile-link" href="profile.php?user=<?= rawurlencode($profileName) ?>">
                                <img src="<?= e(avatar_render_url($selectedSkin['uuid'], 36)) ?>" alt="">
                                <span><?= e($profileName) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <div class="action-row">
                        <a class="btn btn--primary js-auth-gate" href="register.php?gate=like&next=<?= $authNext ?>" data-auth-action="like">Like skin</a>
                        <button class="btn btn--ghost js-copy-url" type="button">Copy link</button>
                    </div>
                </div>

                <div class="card">
                    <div class="card__title-row">
                        <h2>Compatibility</h2>
                        <span class="badge-soft">Ready</span>
                    </div>
                    <div class="compat-grid">
                        <div class="compat-card">
                            <strong>Java Edition</strong>
                            <span>Perfect for premium profile discovery and launcher references.</span>
                        </div>
                        <div class="compat-card">
                            <strong>Render exports</strong>
                            <span>Use front, bust or full renders for catalog cards and profile pages.</span>
                        </div>
                        <div class="compat-card">
                            <strong>Backend ready</strong>
                            <span>Prepared to persist searches, relations and stats into MySQL.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section reveal">
        <div class="section-heading">
            <h2>Related skins</h2>
            <p>More official Minecraft renders with quick access to the detail view.</p>
        </div>
        <div class="skins-grid skins-grid--compact">
            <?php foreach ($relatedSkins as $skin): ?>
                <a class="skin-card" href="index.php?page=skin&skin=<?= e($skin['slug']) ?>">
                    <div class="skin-card__head">
                        <span><?= e($skin['name']) ?></span>
                        <small>#<?= e(substr($skin['uuid'], 0, 4)) ?></small>
                    </div>
                    <div class="skin-card__body skin-card__body--compact image-shell">
                        <div class="skin-card__aura" style="--accent: <?= e($skin['accent']) ?>"></div>
                        <img class="skin-card__texture" src="<?= e(assembled_skin_url($skin['uuid'], (string) ($skin['texture_hash'] ?? ''), $skin['variant'], 256, 'frontfull')) ?>" alt="<?= e($skin['name']) ?> front render">
                    </div>
                    <div class="skin-card__meta">
                        <span><?= e($skin['owner']) ?></span>
                        <span><?= e((string) $skin['likes']) ?> likes</span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="cta-banner reveal">
        <div>
            <div class="eyebrow">Keep exploring</div>
            <h2>Track another Minecraft profile right now</h2>
            <p>Search another username or jump back to the catalog to inspect more official renders.</p>
        </div>
        <div class="cta-banner__actions">
            <a class="btn btn--primary" href="index.php?page=skins">Open catalog</a>
            <a class="btn btn--ghost" href="#site-search">Use search</a>
        </div>
    </section>

<?php else: ?>
    <section class="hero hero--home reveal">
            <div class="hero__copy hero__copy--premium">
                <div class="eyebrow">Minecraft profile platform</div>
                <h1>Discover official skins with a site that now feels launch-ready</h1>
                <p>
                    SkinForge now combines live Mojang lookups, assembled renders, a real 3D viewer, auth-gated premium actions,
                    richer profiles and a broader catalog architecture built to feel like a commercial Minecraft platform.
                </p>
            <div class="hero__cta">
                <a class="btn btn--primary" href="index.php?page=skins">Explore skins</a>
                <a class="btn btn--ghost" href="capes.php">Browse capes</a>
            </div>
            <div class="hero__metric-grid">
                <div class="hero-metric">
                    <strong><?= e((string) count($skins)) ?></strong>
                    <span>Official entries</span>
                </div>
                <div class="hero-metric">
                    <strong><?= e((string) $totalProfiles) ?></strong>
                    <span>Linked profiles</span>
                </div>
                <div class="hero-metric">
                    <strong><?= e(number_format($totalViews)) ?></strong>
                    <span>Tracked views</span>
                </div>
            </div>
            <?php if ($liveSearchSkin !== null): ?>
                <div class="search-highlight card">
                    <div>
                        <strong>Latest imported result</strong>
                        <span><?= e($liveSearchSkin['owner']) ?> was added with official texture data and direct render access.</span>
                    </div>
                    <a class="btn btn--primary" href="index.php?page=skin&skin=<?= e($liveSearchSkin['slug']) ?>">Open detail</a>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($spotlightSkin !== null): ?>
            <div class="hero__visual card">
                <div class="hero-spotlight">
                    <div class="hero-spotlight__top">
                        <span class="eyebrow">Spotlight skin</span>
                        <span class="badge-soft">Live render</span>
                    </div>
                    <div class="hero-spotlight__stage image-shell image-shell--hero">
                        <div class="skin-stage__glow" style="--accent: <?= e($spotlightSkin['accent']) ?>"></div>
                        <img src="<?= e(assembled_skin_url($spotlightSkin['uuid'], (string) ($spotlightSkin['texture_hash'] ?? ''), $spotlightSkin['variant'], 512, 'full')) ?>" alt="<?= e($spotlightSkin['name']) ?> spotlight render">
                    </div>
                    <div class="hero-spotlight__meta">
                        <div>
                            <strong><?= e($spotlightSkin['name']) ?></strong>
                            <span><?= e($spotlightSkin['owner']) ?> • <?= e($spotlightSkin['variant']) ?> model</span>
                        </div>
                        <a class="btn btn--primary" href="index.php?page=skin&skin=<?= e($spotlightSkin['slug']) ?>">Open skin</a>
                    </div>
                    <div class="hero-spotlight__stats">
                        <div><strong><?= e((string) $spotlightSkin['likes']) ?></strong><span>Likes</span></div>
                        <div><strong><?= e((string) count($spotlightSkin['profiles'])) ?></strong><span>Profiles</span></div>
                        <div><strong><?= e((string) $spotlightSkin['views']) ?></strong><span>Views</span></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <section class="trust-strip reveal">
        <div class="trust-card">
            <strong>Official texture pipeline</strong>
            <span>Built around Mojang profile and texture resolution.</span>
        </div>
        <div class="trust-card">
            <strong>Interactive 3D viewer</strong>
            <span>Inspect skins with animated playback, camera presets and official texture sources.</span>
        </div>
        <div class="trust-card">
            <strong>Persistence ready</strong>
            <span>Prepared to save searches and profile links into MySQL.</span>
        </div>
        <div class="trust-card">
            <strong>Product-quality UI</strong>
            <span>Premium layout, smoother loading states and stronger hierarchy.</span>
        </div>
    </section>

    <section class="section reveal">
        <div class="section-heading center">
            <div class="eyebrow">Creator workflow</div>
            <h2>From discovery to profile studio in one ecosystem</h2>
            <p>SkinForge now feels stronger end-to-end: discover skins, inspect renders, save favorites and build a personal creator profile.</p>
        </div>
        <div class="feature-grid">
            <article class="feature-card card">
                <strong>Search and import</strong>
                <p>Pull real Minecraft identities into the experience and turn a simple search into a richer discovery flow.</p>
            </article>
            <article class="feature-card card">
                <strong>Inspect and compare</strong>
                <p>Use 3D, 2D, camera presets and cinematic viewing to understand a skin before saving or sharing it.</p>
            </article>
            <article class="feature-card card">
                <strong>Launch your studio</strong>
                <p>Create an account, connect your nickname and build a profile that feels like a public creator destination.</p>
            </article>
        </div>
    </section>

    <section class="section reveal">
        <div class="section-heading">
            <h2>Trending now</h2>
            <p>Freshly tracked skins with polished front renders and linked profile counts.</p>
        </div>
        <div class="showcase-strip">
            <?php foreach (array_slice($skins, 0, 4) as $skin): ?>
                <a class="showcase-card" href="index.php?page=skin&skin=<?= e($skin['slug']) ?>">
                    <div class="image-shell image-shell--showcase">
                        <img src="<?= e(assembled_skin_url($skin['uuid'], (string) ($skin['texture_hash'] ?? ''), $skin['variant'], 256, 'frontfull')) ?>" alt="<?= e($skin['name']) ?> front render">
                    </div>
                    <div>
                        <strong><?= e($skin['name']) ?></strong>
                        <span><?= e((string) count($skin['profiles'])) ?> linked profiles</span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="section reveal">
        <div class="section-heading center">
            <div class="eyebrow">Built like a product</div>
            <h2>Everything needed to feel like a real platform</h2>
            <p>Not just a gallery: SkinForge is structured as a discoverable, searchable and expandable skin ecosystem.</p>
        </div>
        <div class="feature-grid">
            <article class="feature-card card">
                <strong>Live profile ingestion</strong>
                <p>Search by username and pull profile texture data into the experience with persistence-ready metadata.</p>
            </article>
            <article class="feature-card card">
                <strong>Better discovery flow</strong>
                <p>Use tags, spotlight sections, related skins and creator-style rows to move through the catalog naturally.</p>
            </article>
            <article class="feature-card card">
                <strong>Account-led conversion</strong>
                <p>Likes and downloads now move visitors into sign-up flows, helping the platform feel monetizable and product-driven.</p>
            </article>
        </div>
    </section>

    <section class="content-layout section">
        <div class="content-main reveal">
            <div class="section-heading">
                <h2><?= e($catalogHeadline) ?></h2>
                <p>Browse real Minecraft profiles, save references and jump into interactive render views.</p>
            </div>
            <div class="skins-grid">
                <?php foreach ($filteredSkins as $index => $skin): ?>
                    <a class="skin-card" href="index.php?page=skin&skin=<?= e($skin['slug']) ?>">
                        <div class="skin-card__head">
                            <span><?= e($skin['name']) ?></span>
                            <small><?= e($skin['variant']) ?></small>
                        </div>
                        <div class="skin-card__body image-shell">
                            <div class="skin-card__aura" style="--accent: <?= e($skin['accent']) ?>"></div>
                            <img class="skin-card__texture" src="<?= e(assembled_skin_url($skin['uuid'], (string) ($skin['texture_hash'] ?? ''), $skin['variant'], 256, 'frontfull')) ?>" alt="<?= e($skin['name']) ?> front render">
                        </div>
                        <div class="skin-card__meta">
                            <span>#<?= e((string) ($index + 1)) ?></span>
                            <span><?= e((string) count($skin['profiles'])) ?> profiles</span>
                        </div>
                        <div class="skin-card__footer">
                            <span><?= e($skin['owner']) ?></span>
                            <span><?= e(number_format((int) $skin['likes'])) ?> likes</span>
                            <span><?= e(number_format((int) $skin['views'])) ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <aside class="content-sidebar reveal">
            <div class="card sidebar-card">
                <div class="card__title-row">
                    <h3>Featured</h3>
                    <span class="badge-soft">Creators</span>
                </div>
                <?php foreach ($featuredProfiles as $profile): ?>
                    <div class="mini-row">
                        <span><?= e($profile['name']) ?></span>
                        <small><?= e($profile['score']) ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="card sidebar-card">
                <div class="card__title-row">
                    <h3>Popular</h3>
                    <span class="badge-soft">Hot</span>
                </div>
                <?php foreach ($popularProfiles as $profile): ?>
                    <div class="mini-row">
                        <span><?= e($profile['name']) ?></span>
                        <small><?= e($profile['score']) ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="card sidebar-card">
                <div class="card__title-row">
                    <h3>Recent searches</h3>
                    <span class="badge-soft">Live feel</span>
                </div>
                <?php foreach ($recentSearches as $item): ?>
                    <div class="search-log">
                        <div>
                            <strong><?= e($item['query']) ?></strong>
                            <small><?= e($item['source']) ?></small>
                        </div>
                        <span><?= e($item['time']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </aside>
    </section>

    <section class="section reveal">
        <div class="section-heading">
            <h2>Platform intelligence</h2>
            <p>Key product signals that make the experience feel reliable, data-backed and ready to scale.</p>
        </div>
        <div class="insights-grid">
            <article class="insight-card card">
                <strong><?= e(number_format($totalViews)) ?></strong>
                <span>Total tracked views across the catalog.</span>
            </article>
            <article class="insight-card card">
                <strong><?= e((string) $totalProfiles) ?></strong>
                <span>Profiles currently linked to a skin entry.</span>
            </article>
            <article class="insight-card card">
                <strong><?= $pdo !== null ? 'Connected' : 'Ready' ?></strong>
                <span><?= $pdo !== null ? 'MySQL mode is online and available.' : 'Database schema is ready for activation.' ?></span>
            </article>
        </div>
    </section>

    <section class="cta-banner reveal">
        <div>
            <div class="eyebrow">Creator studio</div>
            <h2>Build a personalized Minecraft profile, not just a saved list</h2>
            <p>Open your profile studio, connect a nickname, customize the blocks and turn the product into a richer account experience.</p>
        </div>
        <div class="cta-banner__actions">
            <a class="btn btn--primary js-profile-link" href="profile.php">Open profile studio</a>
            <a class="btn btn--ghost" href="register.php">Create account</a>
        </div>
    </section>

    <section class="section section--faq reveal">
        <div class="section-heading">
            <h2>Why SkinForge works</h2>
            <p>
                SkinForge is structured as a real product: search pipeline, render views, linked profiles and reusable data blocks that can scale to production.
            </p>
        </div>
        <div class="faq-list">
            <?php foreach ($faq as $item): ?>
                <article class="faq-item card">
                    <h3><?= e($item['question']) ?></h3>
                    <p><?= e($item['answer']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="cta-banner reveal">
        <div>
            <div class="eyebrow">SkinForge studio</div>
            <h2>Ready to turn this into a full Minecraft skin product?</h2>
            <p>Keep the current premium UI, connect the database and expand the search pipeline into a production-ready platform.</p>
        </div>
        <div class="cta-banner__actions">
            <a class="btn btn--primary" href="contact.php">Contact</a>
            <a class="btn btn--ghost" href="servers.php">See more sections</a>
        </div>
    </section>
<?php endif; ?>

<?php render_footer(); ?>
