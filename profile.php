<?php

declare(strict_types=1);

require __DIR__ . '/includes/data.php';
require __DIR__ . '/includes/layout.php';

$fallbackSkin = $skins[3] ?? $skins[0];
$fallbackTextureHash = (string) ($fallbackSkin['texture_hash'] ?? '');
$fallbackTextureUrl = skin_texture_url($fallbackSkin['owner'], $fallbackSkin['uuid'], $fallbackTextureHash, 512);
$relatedCollection = array_slice(array_values(array_filter(
    $skins,
    static fn(array $skin): bool => $skin['slug'] !== $fallbackSkin['slug']
)), 0, 6);

$skinCatalogPayload = array_map(static function (array $skin): array {
    $textureHash = (string) ($skin['texture_hash'] ?? '');

    return [
        'slug' => $skin['slug'],
        'name' => $skin['name'],
        'owner' => $skin['owner'],
        'uuid' => $skin['uuid'],
        'variant' => strtolower($skin['variant']),
        'textureUrl' => skin_texture_url($skin['owner'], $skin['uuid'], $textureHash, 512),
        'frontRenderUrl' => assembled_skin_url($skin['uuid'], $textureHash, $skin['variant'], 512, 'frontfull'),
        'profiles' => $skin['profiles'],
    ];
}, $skins);

render_header('profile', 'Profile - SkinForge');
?>
<section class="profile-shell reveal">
    <script id="profile-skin-catalog" type="application/json"><?= json_encode($skinCatalogPayload, JSON_UNESCAPED_SLASHES) ?></script>

    <div class="notice notice--info profile-gate js-profile-gate" hidden>
        Create or log into a SkinForge account to unlock your editable profile studio.
    </div>

    <div class="profile-hero card">
        <div class="profile-hero__identity">
            <div class="profile-avatar image-shell is-ready">
                <img class="js-profile-avatar" src="<?= e(avatar_render_url($fallbackSkin['uuid'], 88)) ?>" alt="Profile avatar">
            </div>
            <div>
                <div class="eyebrow js-profile-status">Creator profile</div>
                <h1 class="js-profile-name">Your SkinForge Studio</h1>
                <p class="js-profile-headline">Build your own premium Minecraft profile, connect your skin and customize every block of your public page.</p>
                <div class="detail-chip-row">
                    <span class="detail-chip js-profile-handle">@yourstudio</span>
                    <span class="detail-chip js-current-skin-variant"><?= e($fallbackSkin['variant']) ?> model</span>
                    <span class="detail-chip">Mojang ready</span>
                </div>
            </div>
        </div>
        <div class="profile-hero__stats">
            <div><strong class="js-stat-followers">1.2K</strong><span>Followers</span></div>
            <div><strong class="js-stat-collections">8</strong><span>Collections</span></div>
            <div><strong class="js-stat-likes">540</strong><span>Total likes</span></div>
        </div>
    </div>

    <div class="profile-tabs card">
        <span class="profile-tab is-active">Information</span>
        <span class="profile-tab">Capes</span>
        <span class="profile-tab">Optifine Capes</span>
        <span class="profile-tab">Skins</span>
        <span class="profile-tab">Favourite Servers</span>
    </div>

    <div class="profile-overview-grid reveal">
        <article class="profile-overview-card card">
            <span class="eyebrow">Studio completion</span>
            <strong>84%</strong>
            <p>Your profile already supports connected skins, editable copy, creator actions and showcase-ready blocks.</p>
        </article>
        <article class="profile-overview-card card">
            <span class="eyebrow">Current focus</span>
            <strong>Creator launch</strong>
            <p>Connect your favorite nickname, finish your links and turn this into a public creator hub.</p>
        </article>
        <article class="profile-overview-card card">
            <span class="eyebrow">Inventory power</span>
            <strong>Skins + capes</strong>
            <p>Use the profile as a base for future inventories, drops, social flex and saved cosmetic bundles.</p>
        </article>
    </div>

    <div class="profile-marquee card reveal">
        <div class="profile-marquee__copy">
            <span class="eyebrow">Creator launch kit</span>
            <h2>Turn your profile into a polished Minecraft identity hub</h2>
            <p>
                SkinForge profile pages now mix interactive skin viewing, editable creator data, social blocks, loadout modules,
                collection previews and growth panels so the account feels much closer to a real platform.
            </p>
            <div class="detail-chip-row">
                <span class="detail-chip js-profile-theme">Aurora Mint</span>
                <span class="detail-chip js-profile-mode">BedWars</span>
                <span class="detail-chip js-profile-location">Global</span>
                <span class="detail-chip js-profile-cape">No cape equipped</span>
            </div>
        </div>
        <div class="profile-marquee__stats">
            <div><strong>12</strong><span>Custom blocks</span></div>
            <div><strong>4</strong><span>Viewer modes</span></div>
            <div><strong>300+</strong><span>Catalog skins</span></div>
            <div><strong>Ready</strong><span>For showcase</span></div>
        </div>
    </div>

    <div class="profile-layout">
        <div class="profile-main">
            <div class="card profile-stage-card">
                <div class="card__title-row">
                    <h2>Your connected Minecraft skin</h2>
                    <span class="badge-soft js-connected-skin-name"><?= e($fallbackSkin['name']) ?></span>
                </div>
                <div class="profile-stage-layout">
                    <div>
                        <div
                            class="skin-stage image-shell image-shell--large js-skin-viewer js-profile-stage profile-viewer"
                            data-skin-url="<?= e($fallbackTextureUrl) ?>"
                            data-variant="<?= e(strtolower($fallbackSkin['variant'])) ?>"
                            data-name="<?= e($fallbackSkin['name']) ?>"
                        >
                            <div class="cinema-bars" aria-hidden="true"></div>
                            <div class="viewer-hud">
                                <span class="viewer-hud__chip js-viewer-mode">IDLE MODE</span>
                                <span class="viewer-hud__chip js-viewer-camera">FRONT CAM</span>
                            </div>
                            <div class="skin-stage__glow" style="--accent: <?= e($fallbackSkin['accent']) ?>"></div>
                            <canvas class="skin-stage__canvas js-skin-canvas" width="520" height="520" aria-label="3D profile viewer"></canvas>
                            <img class="skin-stage__render js-render-fallback" src="<?= e(assembled_skin_url($fallbackSkin['uuid'], $fallbackTextureHash, $fallbackSkin['variant'], 512, 'frontfull')) ?>" alt="<?= e($fallbackSkin['name']) ?> render" hidden>
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
                                <button class="render-control viewer-control viewer-control--accent" type="button" data-viewer-cycle="true">Play reel</button>
                            </div>
                        </div>
                    </div>

                    <div class="profile-stage-side">
                        <div class="profile-media-card">
                            <div class="profile-media-card__video">
                                <span class="profile-media-card__play">▶</span>
                                <span>Creator guide showcase</span>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card__title-row">
                                <h3>Head command</h3>
                                <span class="badge-soft">1.21+</span>
                            </div>
                            <div class="command-copy">
                                <code class="js-head-command">/give @p minecraft:player_head[profile="Minecraft"]</code>
                                <button class="btn btn--ghost js-copy-head" type="button">Copy</button>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card__title-row">
                                <h3>Connected skin info</h3>
                                <span class="badge-soft">Live</span>
                            </div>
                            <div class="info-list">
                                <div class="info-list__row"><span>Nickname</span><strong class="js-connected-skin-owner"><?= e($fallbackSkin['owner']) ?></strong></div>
                                <div class="info-list__row"><span>UUID</span><strong class="js-connected-skin-uuid"><?= e($fallbackSkin['uuid']) ?></strong></div>
                                <div class="info-list__row"><span>Model</span><strong class="js-connected-skin-variant"><?= e($fallbackSkin['variant']) ?></strong></div>
                            </div>
                            <div class="action-row">
                                <a class="btn btn--primary js-open-skin-link" href="index.php?page=skin&skin=<?= e($fallbackSkin['slug']) ?>">Open skin page</a>
                                <button class="btn btn--ghost js-copy-url" type="button">Copy profile link</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card profile-connect-card">
                <div class="card__title-row">
                    <h2>Connect your skin by nickname</h2>
                    <span class="badge-soft">Minecraft identity</span>
                </div>
                <p class="card-copy">Type a nickname from the catalog and SkinForge will connect that skin to your personal profile studio.</p>
                <form class="profile-connect-form js-skin-connect-form">
                    <input class="js-skin-connect-input" name="nickname" type="text" list="profile-skin-options" placeholder="Example: Dream, Skeppy, TommyInnit">
                    <datalist id="profile-skin-options">
                        <?php foreach ($skins as $skin): ?>
                            <option value="<?= e($skin['owner']) ?>"></option>
                            <option value="<?= e($skin['name']) ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                    <button class="btn btn--primary" type="submit">Connect skin</button>
                </form>
                <div class="auth-feedback js-skin-connect-feedback" hidden></div>
            </div>

            <div class="card profile-editor js-profile-editor" hidden>
                <div class="card__title-row">
                    <h2>Customize your profile</h2>
                    <span class="badge-soft">Studio controls</span>
                </div>
                <form class="profile-form js-profile-form">
                    <label>Display name<input name="username" type="text" value="Your SkinForge Studio"></label>
                    <label>Headline<input name="headline" type="text" value="Build your own premium Minecraft profile, connect your skin and customize every block of your public page."></label>
                    <label>Handle<input name="handle" type="text" value="@yourstudio"></label>
                    <label>Status<input name="status" type="text" value="Creator"></label>
                    <label>Favorite server<input name="favoriteServer" type="text" value="mc.hypixel.net"></label>
                    <div class="profile-form__grid profile-form__grid--quad">
                        <label>Location<input name="location" type="text" value="Global"></label>
                        <label>Favorite mode<input name="favoriteMode" type="text" value="BedWars"></label>
                        <label>Theme<input name="theme" type="text" value="Aurora Mint"></label>
                        <label>Favorite cape<input name="favoriteCape" type="text" value="No cape equipped"></label>
                    </div>
                    <label>Bio<textarea name="bio" rows="4">This is my custom SkinForge studio where I save skins, capes, renders and creator-ready Minecraft profile assets.</textarea></label>
                    <div class="profile-form__grid">
                        <label>Followers<input name="followers" type="text" value="1.2K"></label>
                        <label>Collections<input name="collections" type="text" value="8"></label>
                        <label>Likes<input name="likes" type="text" value="540"></label>
                    </div>
                    <div class="profile-form__grid profile-form__grid--triple">
                        <label>Discord<input name="discord" type="text" value="Not connected"></label>
                        <label>YouTube<input name="youtube" type="text" value="Not connected"></label>
                        <label>TikTok<input name="tiktok" type="text" value="Not connected"></label>
                    </div>
                    <div class="auth-feedback js-profile-feedback" hidden></div>
                    <div class="action-row">
                        <button class="btn btn--primary js-profile-save" type="submit">Save profile</button>
                        <button class="btn btn--ghost js-profile-reset" type="button">Reset</button>
                    </div>
                </form>
            </div>

            <div class="profile-panels">
                <div class="card">
                    <div class="card__title-row">
                        <h2>Bio</h2>
                        <span class="badge-soft">Editable</span>
                    </div>
                    <p class="js-profile-bio">This is my custom SkinForge studio where I save skins, capes, renders and creator-ready Minecraft profile assets.</p>
                </div>
                <div class="card">
                    <div class="card__title-row">
                        <h2>Information</h2>
                        <span class="badge-soft">Live data</span>
                    </div>
                    <div class="info-list">
                        <div class="info-list__row"><span>Status</span><strong class="js-profile-status-line">Creator</strong></div>
                        <div class="info-list__row"><span>Location</span><strong class="js-profile-location">Global</strong></div>
                        <div class="info-list__row"><span>Favorite mode</span><strong class="js-profile-mode">BedWars</strong></div>
                        <div class="info-list__row"><span>UUID</span><strong class="js-connected-skin-uuid-panel"><?= e($fallbackSkin['uuid']) ?></strong></div>
                        <div class="info-list__row"><span>Model</span><strong class="js-connected-skin-variant-panel"><?= e($fallbackSkin['variant']) ?></strong></div>
                        <div class="info-list__row"><span>Favourite server</span><strong class="js-favorite-server">mc.hypixel.net</strong></div>
                    </div>
                </div>
                <div class="card">
                    <div class="card__title-row">
                        <h2>Capes</h2>
                        <span class="badge-soft">Inventory</span>
                    </div>
                    <p>No cape connected yet. Equip one from the capes section.</p>
                </div>
                <div class="card">
                    <div class="card__title-row">
                        <h2>Optifine Capes</h2>
                        <span class="badge-soft">Custom</span>
                    </div>
                    <p>No Optifine cape linked yet.</p>
                </div>
                <div class="card">
                    <div class="card__title-row">
                        <h2>Skins</h2>
                        <span class="badge-soft">Collection</span>
                    </div>
                    <div class="skins-grid skins-grid--compact">
                        <?php foreach ($relatedCollection as $skin): ?>
                            <a class="skin-card" href="index.php?page=skin&skin=<?= e($skin['slug']) ?>">
                                <div class="skin-card__head">
                                    <span><?= e($skin['name']) ?></span>
                                    <small><?= e($skin['variant']) ?></small>
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
                </div>
                <div class="card">
                    <div class="card__title-row">
                        <h2>Favourite Servers</h2>
                        <span class="badge-soft">Pinned</span>
                    </div>
                    <p>No favourite servers pinned yet.</p>
                </div>
                <div class="card">
                    <div class="card__title-row">
                        <h2>Creator badges</h2>
                        <span class="badge-soft">Unlocked</span>
                    </div>
                    <div class="detail-chip-row">
                        <span class="detail-chip">3D Builder</span>
                        <span class="detail-chip">Skin Curator</span>
                        <span class="detail-chip">Profile Verified</span>
                    </div>
                </div>
                <div class="card">
                    <div class="card__title-row">
                        <h2>Customization theme</h2>
                        <span class="badge-soft">Studio</span>
                    </div>
                    <p>Current theme: <strong class="js-profile-theme">Aurora Mint</strong>. Ready for future presets, banner swaps and creator color palettes.</p>
                </div>
                <div class="card">
                    <div class="card__title-row">
                        <h2>Social links</h2>
                        <span class="badge-soft">Public</span>
                    </div>
                    <div class="info-list">
                        <div class="info-list__row"><span>Discord</span><strong class="js-profile-discord">Not connected</strong></div>
                        <div class="info-list__row"><span>YouTube</span><strong class="js-profile-youtube">Not connected</strong></div>
                        <div class="info-list__row"><span>TikTok</span><strong class="js-profile-tiktok">Not connected</strong></div>
                    </div>
                </div>
                <div class="card">
                    <div class="card__title-row">
                        <h2>Equipped loadout</h2>
                        <span class="badge-soft">Active</span>
                    </div>
                    <div class="detail-chip-row">
                        <span class="detail-chip js-profile-cape">No cape equipped</span>
                        <span class="detail-chip js-profile-theme">Aurora Mint</span>
                        <span class="detail-chip js-profile-mode">BedWars</span>
                    </div>
                    <p class="card-copy">Use this block to show the current combination of skin, cape, theme and favorite game mode.</p>
                </div>
                <div class="card">
                    <div class="card__title-row">
                        <h2>Profile goals</h2>
                        <span class="badge-soft">Progress</span>
                    </div>
                    <div class="info-list">
                        <div class="info-list__row"><span>Connect a cape</span><strong>Pending</strong></div>
                        <div class="info-list__row"><span>Save 10 skins</span><strong>In progress</strong></div>
                        <div class="info-list__row"><span>Publish studio</span><strong>Ready</strong></div>
                    </div>
                </div>
                <div class="card">
                    <div class="card__title-row">
                        <h2>Engagement pulse</h2>
                        <span class="badge-soft">Weekly</span>
                    </div>
                    <div class="info-list">
                        <div class="info-list__row"><span>Profile visits</span><strong>184</strong></div>
                        <div class="info-list__row"><span>Skin clicks</span><strong>62</strong></div>
                        <div class="info-list__row"><span>Shares</span><strong>19</strong></div>
                    </div>
                </div>
                <div class="card">
                    <div class="card__title-row">
                        <h2>Studio roadmap</h2>
                        <span class="badge-soft">Next</span>
                    </div>
                    <div class="timeline">
                        <div class="timeline__item"><strong>Connect social links</strong><span>Give your profile a more public creator identity.</span></div>
                        <div class="timeline__item"><strong>Equip a rare cape</strong><span>Turn your profile into a stronger cosmetic showcase.</span></div>
                        <div class="timeline__item"><strong>Save more bundles</strong><span>Build a richer inventory and launch-ready profile flow.</span></div>
                    </div>
                </div>
                <div class="card">
                    <div class="card__title-row">
                        <h2>Pinned collection</h2>
                        <span class="badge-soft">Showcase</span>
                    </div>
                    <div class="profile-mini-collection">
                        <?php foreach (array_slice($relatedCollection, 0, 3) as $skin): ?>
                            <a class="profile-mini-card" href="index.php?page=skin&skin=<?= e($skin['slug']) ?>">
                                <img src="<?= e(assembled_skin_url($skin['uuid'], (string) ($skin['texture_hash'] ?? ''), $skin['variant'], 144, 'frontfull')) ?>" alt="<?= e($skin['name']) ?> preview">
                                <strong><?= e($skin['name']) ?></strong>
                                <span><?= e($skin['owner']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="card">
                    <div class="card__title-row">
                        <h2>Creator milestones</h2>
                        <span class="badge-soft">Unlocked</span>
                    </div>
                    <div class="info-list">
                        <div class="info-list__row"><span>Profile ready</span><strong>Completed</strong></div>
                        <div class="info-list__row"><span>Nickname synced</span><strong>Available</strong></div>
                        <div class="info-list__row"><span>Public studio polish</span><strong>Advanced</strong></div>
                    </div>
                </div>
            </div>
        </div>

        <aside class="profile-sidebar">
            <div class="card">
                <div class="card__title-row">
                    <h2>Creator actions</h2>
                    <span class="badge-soft">Account</span>
                </div>
                <div class="quick-tools">
                    <a class="tool-card tool-card--locked js-auth-gate" href="register.php?gate=follow&next=<?= rawurlencode('profile.php') ?>" data-auth-action="follow">Follow creator</a>
                    <a class="tool-card tool-card--locked js-auth-gate" href="register.php?gate=collection&next=<?= rawurlencode('profile.php') ?>" data-auth-action="save">Save collection</a>
                    <a class="tool-card js-open-skin-link" href="index.php?page=skin&skin=<?= e($fallbackSkin['slug']) ?>">Open skin page</a>
                    <button class="tool-card tool-card--button js-copy-url" type="button">Copy profile link</button>
                </div>
            </div>

            <div class="card">
                <div class="card__title-row">
                    <h2>Recent activity</h2>
                    <span class="badge-soft">Today</span>
                </div>
                <div class="timeline">
                    <div class="timeline__item"><strong>Studio initialized</strong><span>Your personal profile is ready to be customized.</span></div>
                    <div class="timeline__item"><strong>Skin connection available</strong><span>Use the nickname connector to sync a Minecraft skin into the viewer.</span></div>
                    <div class="timeline__item"><strong>Creator blocks unlocked</strong><span>Bio, information, commands and social stats can be personalized.</span></div>
                </div>
            </div>

            <div class="card">
                <div class="card__title-row">
                    <h2>Quick shortcuts</h2>
                    <span class="badge-soft">Tools</span>
                </div>
                <div class="quick-tools">
                    <a class="tool-card" href="capes.php">Browse capes</a>
                    <a class="tool-card" href="index.php?page=skins">Open catalog</a>
                    <a class="tool-card js-open-skin-link" href="index.php?page=skin&skin=<?= e($fallbackSkin['slug']) ?>">Current skin</a>
                    <button class="tool-card tool-card--button js-copy-head" type="button">Copy head cmd</button>
                </div>
            </div>

            <div class="card">
                <div class="card__title-row">
                    <h2>Creator reputation</h2>
                    <span class="badge-soft">Ranked</span>
                </div>
                    <div class="info-list">
                        <div class="info-list__row"><span>Studio rank</span><strong>#24</strong></div>
                        <div class="info-list__row"><span>Verified score</span><strong>92</strong></div>
                        <div class="info-list__row"><span>Quality level</span><strong>Premium</strong></div>
                    </div>
                </div>

            <div class="card">
                <div class="card__title-row">
                    <h2>Studio setup</h2>
                    <span class="badge-soft">Live</span>
                </div>
                <div class="info-list">
                    <div class="info-list__row"><span>Theme</span><strong class="js-profile-theme">Aurora Mint</strong></div>
                    <div class="info-list__row"><span>Location</span><strong class="js-profile-location">Global</strong></div>
                    <div class="info-list__row"><span>Favourite mode</span><strong class="js-profile-mode">BedWars</strong></div>
                </div>
            </div>

            <div class="card">
                <div class="card__title-row">
                    <h2>Social hub</h2>
                    <span class="badge-soft">Editable</span>
                </div>
                <div class="info-list">
                    <div class="info-list__row"><span>Discord</span><strong class="js-profile-discord">Not connected</strong></div>
                    <div class="info-list__row"><span>YouTube</span><strong class="js-profile-youtube">Not connected</strong></div>
                    <div class="info-list__row"><span>TikTok</span><strong class="js-profile-tiktok">Not connected</strong></div>
                </div>
            </div>
        </aside>
    </div>
</section>
<?php render_footer(); ?>
