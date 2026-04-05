<?php

declare(strict_types=1);

require __DIR__ . '/includes/data.php';
require __DIR__ . '/includes/layout.php';

$featuredCape = $capes[0];
$capeCount = count($capes);

render_header('capes', 'Capes - SkinForge');
?>
<section class="hero hero--home reveal">
    <div class="hero__copy hero__copy--premium">
        <div class="eyebrow">Minecraft cape studio</div>
        <h1>Rare capes, event drops and profile-ready cosmetics</h1>
        <p>
            SkinForge now gives capes their own premium destination with rarity, seasonal drops,
            collectible presentation and stronger discovery flow for profiles and showcases.
        </p>
        <div class="hero__cta">
            <a class="btn btn--primary" href="#cape-catalog">Open cape catalog</a>
            <a class="btn btn--ghost js-profile-link" href="profile.php">Use on profile</a>
        </div>
        <div class="hero__metric-grid">
            <div class="hero-metric">
                <strong><?= e((string) $capeCount) ?></strong>
                <span>Cape drops</span>
            </div>
            <div class="hero-metric">
                <strong>4</strong>
                <span>Rarity tiers</span>
            </div>
            <div class="hero-metric">
                <strong>Live</strong>
                <span>Profile ready</span>
            </div>
        </div>
    </div>

    <div class="hero__visual card">
        <div class="cape-hero" style="--accent: <?= e($featuredCape['accent']) ?>">
            <div class="cape-hero__preview"></div>
            <div class="cape-hero__meta">
                <div>
                    <strong><?= e($featuredCape['name']) ?></strong>
                    <span><?= e($featuredCape['season']) ?> • <?= e($featuredCape['rarity']) ?></span>
                </div>
                <a class="btn btn--primary js-auth-gate" href="register.php?gate=cape&next=<?= rawurlencode('capes.php') ?>" data-auth-action="cape">Save cape</a>
            </div>
        </div>
    </div>
</section>

<section class="trust-strip reveal">
    <div class="trust-card">
        <strong>Rarity system</strong>
        <span>Legendary, Mythic, Epic and Rare layers for cleaner collection browsing.</span>
    </div>
    <div class="trust-card">
        <strong>Profile integration</strong>
        <span>Designed to connect cleanly into creator profiles and inventory flows.</span>
    </div>
    <div class="trust-card">
        <strong>Premium presentation</strong>
        <span>Better cards, better spacing and stronger visual hierarchy for cosmetics.</span>
    </div>
    <div class="trust-card">
        <strong>Client-ready feel</strong>
        <span>The cape section now reads like a real product surface instead of a simple demo grid.</span>
    </div>
</section>

<section id="cape-catalog" class="section reveal">
    <div class="section-heading">
        <h2>Cape catalog</h2>
        <p>Browse collectible layers designed for profile flex, event memory and premium cosmetic identity.</p>
    </div>
    <div class="cape-grid">
        <?php foreach ($capes as $cape): ?>
            <article class="cape-card card" style="--accent: <?= e($cape['accent']) ?>">
                <div class="cape-card__top">
                    <span class="badge-soft"><?= e($cape['rarity']) ?></span>
                    <small><?= e($cape['season']) ?></small>
                </div>
                <div class="cape-preview"></div>
                <h3><?= e($cape['name']) ?></h3>
                <p>Powered by <?= e($cape['owner']) ?></p>
                <div class="cape-card__footer">
                    <span>Creator cosmetic</span>
                    <a class="btn btn--ghost js-auth-gate" href="register.php?gate=cape&next=<?= rawurlencode('capes.php') ?>" data-auth-action="cape">Equip</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section reveal">
    <div class="section-heading center">
        <div class="eyebrow">Why this section hits harder</div>
        <h2>Built like a cosmetic store, not a placeholder</h2>
        <p>Richer information, stronger spotlighting and gated actions make the cape area feel much more commercial.</p>
    </div>
    <div class="feature-grid">
        <article class="feature-card card">
            <strong>Event-based drops</strong>
            <p>Each cape now carries season and rarity context to support a more collectible ecosystem.</p>
        </article>
        <article class="feature-card card">
            <strong>Profile-first utility</strong>
            <p>Ideal for future account inventories, social flex cards and creator identity surfaces.</p>
        </article>
        <article class="feature-card card">
            <strong>Better visual flow</strong>
            <p>Hero, catalog and CTA flow make this section feel intentionally designed instead of secondary.</p>
        </article>
    </div>
</section>
<?php render_footer(); ?>
