<?php

declare(strict_types=1);

require __DIR__ . '/includes/data.php';
require __DIR__ . '/includes/layout.php';

$gate = isset($_GET['gate']) ? trim((string) $_GET['gate']) : '';
$next = isset($_GET['next']) ? (string) $_GET['next'] : '';
$notice = $gate !== ''
    ? 'Create your account to unlock ' . $gate . ', keep your favorites and access premium exports inside SkinForge.'
    : 'Create a Minecraft-inspired SkinForge identity with collections, likes and profile activity.';

render_header('register', 'Register - SkinForge');
?>
<section class="auth-shell auth-shell--epic reveal">
    <div class="auth-scene">
        <div class="auth-scene__copy auth-scene__copy--dark">
            <div class="eyebrow">Create your identity</div>
            <h1>Join SkinForge and unlock the full platform</h1>
            <p><?= e($notice) ?></p>
            <div class="detail-stat detail-stat--triple">
                <div><strong>3D</strong><span>Viewer controls</span></div>
                <div><strong>100+</strong><span>Catalog skins</span></div>
                <div><strong>Live</strong><span>Mojang-ready data</span></div>
            </div>
        </div>

        <div class="card auth-card auth-card--epic">
            <div class="auth-card__brand">
                <span class="brand__badge">S</span>
                <strong>SkinForge Create Account</strong>
            </div>
            <?php if ($gate !== ''): ?>
                <div class="notice notice--info auth-notice">This action requires an account before you can continue.</div>
            <?php endif; ?>
            <form class="auth-form js-register-form">
                <label>Username<input name="username" type="text" placeholder="SkinForgePlayer"></label>
                <label>Email<input name="email" type="email" placeholder="player@skinforge.gg"></label>
                <label>Choose your era</label>
                <div class="year-grid">
                    <button type="button" class="btn btn--year">Explorer</button>
                    <button type="button" class="btn btn--year">Builder</button>
                    <button type="button" class="btn btn--year">Creator</button>
                </div>
                <label>Password<input name="password" type="password" placeholder="Create a password"></label>
                <label>Confirm Password<input name="confirm_password" type="password" placeholder="Repeat your password"></label>
                <label>Motto<input name="motto" type="text" placeholder="Builder of premium Minecraft skins"></label>
                <div class="auth-links-row">
                    <a href="login.php<?= $gate !== '' ? '?gate=' . rawurlencode($gate) : '' ?>">Already have an account?</a>
                    <a class="js-profile-link" href="profile.php">Preview profile</a>
                </div>
                <div class="auth-feedback js-auth-feedback" hidden></div>
                <button class="btn btn--primary btn--wide js-register-submit" type="submit" data-next="<?= e($next) ?>">Create SkinForge account</button>
            </form>
            <div class="auth-card__footer">
                <span>Designed to feel commercial, immersive and ready for client review.</span>
            </div>
        </div>
    </div>
</section>
<?php render_footer(); ?>
