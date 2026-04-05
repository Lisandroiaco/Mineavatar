<?php

declare(strict_types=1);

function asset_url(string $path): string
{
    return htmlspecialchars($path, ENT_QUOTES, 'UTF-8');
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function body_render_url(string $uuid, int $size = 180): string
{
    return 'render-skin.php?seed=' . rawurlencode($uuid) . '&size=' . $size;
}

function skin_texture_url(string $owner, string $uuid = '', string $textureHash = '', int $size = 180): string
{
    if ($textureHash !== '') {
        return 'https://textures.minecraft.net/texture/' . rawurlencode($textureHash);
    }

    return 'mojang-texture.php?username=' . rawurlencode($owner) . '&uuid=' . rawurlencode($uuid) . '&size=' . $size;
}

function assembled_skin_url(string $uuid = '', string $textureHash = '', string $variant = 'Classic', int $size = 320, string $mode = 'full'): string
{
    $subject = $textureHash !== '' ? $textureHash : $uuid;
    $model = strtolower($variant) === 'slim' ? 'slim' : 'wide';

    if ($subject === '') {
        return 'render-skin.php?seed=mineavatar&size=' . $size;
    }

    return 'https://visage.surgeplay.com/' . rawurlencode($mode) . '/' . $size . '/' . rawurlencode($subject) . '.png?' . $model . '&no=shadow,cape';
}

function avatar_render_url(string $uuid, int $size = 40): string
{
    return 'render-skin.php?mode=avatar&seed=' . rawurlencode($uuid) . '&size=' . $size;
}

function render_header(string $activePage, string $title = 'SkinForge'): void
{
    $nav = [
        'about' => 'About',
        'skins' => 'Skins',
        'capes' => 'Capes',
        'servers' => 'Servers',
        'contact' => 'Contact',
    ];
    $bodyClass = 'page page--' . preg_replace('/[^a-z0-9_-]+/i', '-', strtolower($activePage !== '' ? $activePage : 'home'));
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?></title>
    <link rel="stylesheet" href="<?= asset_url('style.css') ?>">
</head>
<body class="<?= e($bodyClass) ?>">
<div class="page-loader" aria-hidden="true">
    <div class="page-loader__logo">S</div>
    <div class="page-loader__bar"><span></span></div>
    <strong class="page-loader__percent js-loader-percent">12%</strong>
    <p class="js-loader-message">Loading SkinForge studio...</p>
</div>
<div class="topline">
    <a class="discord-pill" href="https://discord.com" target="_blank" rel="noreferrer">Discord</a>
    <div class="auth-links auth-links--account">
        <span class="account-chip js-account-chip">Guest mode</span>
        <a class="js-login-link" href="login.php">Log In</a>
        <a class="js-register-link" href="register.php">Sign Up</a>
        <a class="js-profile-link" href="profile.php">Profile</a>
        <button class="account-logout js-logout-button" type="button" hidden>Log Out</button>
    </div>
</div>
<header class="site-header">
    <div class="site-header__inner">
        <a class="brand" href="index.php">
            <span class="brand__badge">S</span>
            <span class="brand__text">SkinForge</span>
        </a>
        <nav class="main-nav" aria-label="Main navigation">
            <?php foreach ($nav as $page => $label): ?>
                <a class="<?= $activePage === $page ? 'is-active' : '' ?>" href="<?= $page === 'skins' ? 'index.php?page=skins' : $page . '.php' ?>">
                    <?= e($label) ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
    <div class="search-row">
        <form class="search-form" method="get" action="index.php">
            <input type="hidden" name="page" value="skins">
            <label class="sr-only" for="site-search">Search by name, UUID or aspect</label>
            <input id="site-search" name="q" type="search" placeholder="Name / UUID / Aspect" value="<?= isset($_GET['q']) ? e((string) $_GET['q']) : '' ?>">
            <button type="submit" aria-label="Search skins">
                <span>⌕</span>
            </button>
        </form>
    </div>
</header>
<main class="page-shell">
    <?php
}

function render_footer(): void
{
    ?>
</main>
<footer class="site-footer">
    <div>
        <h3>SKINFORGE</h3>
        <a href="about.php">About us</a>
        <a href="index.php?page=skins">Skins</a>
        <a href="capes.php">Capes</a>
        <a href="servers.php">Servers</a>
        <a href="contact.php">Contact</a>
    </div>
    <div>
        <h3>CONNECT</h3>
        <a href="https://discord.com" target="_blank" rel="noreferrer">Discord</a>
        <a href="mailto:hello@example.com">Email</a>
    </div>
    <div>
        <h3>LATEST DROPS</h3>
        <p>Creator skins this week</p>
        <p>Best front renders</p>
        <p>Server-ready profiles</p>
    </div>
</footer>
<script src="https://unpkg.com/skinview3d@3.2.0/bundles/skinview3d.bundle.js"></script>
<script src="<?= asset_url('app.js') ?>"></script>
</body>
</html>
    <?php
}
