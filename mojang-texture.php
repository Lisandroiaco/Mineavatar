<?php

declare(strict_types=1);

require __DIR__ . '/includes/repository.php';

$username = isset($_GET['username']) ? trim((string) $_GET['username']) : '';
$uuid = isset($_GET['uuid']) ? trim((string) $_GET['uuid']) : '';
$fallbackSeed = $uuid !== '' ? $uuid : ($username !== '' ? $username : 'mineavatar');
$fallbackSize = isset($_GET['size']) ? max(80, min(520, (int) $_GET['size'])) : 180;

$query = $uuid !== '' ? $uuid : $username;
$error = null;

if ($query !== '') {
    $profile = resolve_profile_query($query, $error);
    if ($profile !== null) {
        $textureInfo = resolve_texture_data((string) $profile['id'], $error);
        if ($textureInfo !== null && ($textureInfo['texture_hash'] ?? '') !== '') {
            header('Location: https://textures.minecraft.net/texture/' . rawurlencode((string) $textureInfo['texture_hash']), true, 302);
            exit;
        }
    }
}

header('Location: render-skin.php?seed=' . rawurlencode($fallbackSeed) . '&size=' . $fallbackSize, true, 302);
exit;
