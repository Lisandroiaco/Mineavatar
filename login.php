<?php

declare(strict_types=1);

require __DIR__ . '/includes/data.php';
require __DIR__ . '/includes/layout.php';

$gate = isset($_GET['gate']) ? trim((string) $_GET['gate']) : '';
$notice = $gate !== '' ? 'Create your SkinForge access to unlock ' . $gate . ' actions, likes and premium exports.' : 'Enter your SkinForge portal and keep your Minecraft profile synced.';

render_header('login', 'Login - SkinForge');
?>
<section class="auth-shell auth-shell--epic reveal">
    <div class="auth-scene">
        <div class="auth-scene__copy">
            <div class="eyebrow">SkinForge access</div>
            <h1>Log in like a premium Minecraft platform</h1>
            <p><?= e($notice) ?></p>
            <div class="auth-feature-list">
                <div class="auth-feature"><strong>3D viewer memory</strong><span>Keep saved poses, animations and curated reels.</span></div>
                <div class="auth-feature"><strong>Locked actions unlocked</strong><span>Like, download and export front renders from one account.</span></div>
                <div class="auth-feature"><strong>Profile identity</strong><span>Build your own creator page with collections and activity.</span></div>
            </div>
        </div>

        <div class="card auth-card auth-card--epic">
            <div class="auth-card__brand">
                <span class="brand__badge">S</span>
                <strong>SkinForge Sign In</strong>
            </div>
            <form class="auth-form js-login-form">
                <label>Email or username<input name="identity" type="text" placeholder="steve@skinforge.gg"></label>
                <label>Password<input name="password" type="password" placeholder="Enter your password"></label>
                <div class="auth-links-row">
                    <a href="register.php<?= $gate !== '' ? '?gate=' . rawurlencode($gate) : '' ?>">Need an account?</a>
                    <a href="#">Forgot password?</a>
                </div>
                <div class="auth-feedback js-auth-feedback" hidden></div>
                <button class="btn btn--primary btn--wide js-login-submit" type="submit">Enter SkinForge</button>
            </form>
            <div class="auth-card__footer">
                <span>Secure session styling inspired by premium Minecraft identity flows.</span>
            </div>
        </div>
    </div>
</section>
<?php render_footer(); ?>
