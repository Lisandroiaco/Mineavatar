<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

function connect_database(?string &$error = null): ?PDO
{
    $settings = mineavatar_database_settings();

    if ($settings['host'] === '' || $settings['database'] === '' || $settings['username'] === '') {
        return null;
    }

    if (!class_exists(PDO::class)) {
        $error = 'PDO is not available in this PHP environment.';
        return null;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $settings['host'],
        $settings['port'],
        $settings['database'],
        $settings['charset']
    );

    try {
        return new PDO($dsn, $settings['username'], $settings['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $exception) {
        $error = $exception->getMessage();
        return null;
    }
}

function load_skins(array $fallbackSkins, ?PDO $pdo, ?string &$error = null): array
{
    if ($pdo === null) {
        return $fallbackSkins;
    }

    $sql = <<<SQL
        SELECT
            skins.slug,
            skins.uuid,
            skins.skin_name,
            skins.owner_name,
            skins.variant,
            skins.description,
            skins.likes_count,
            skins.views_count,
            skins.texture_hash,
            GROUP_CONCAT(profiles.profile_name ORDER BY profiles.profile_name SEPARATOR ',') AS profile_names
        FROM skins
        LEFT JOIN skin_profiles ON skin_profiles.skin_id = skins.id
        LEFT JOIN profiles ON profiles.id = skin_profiles.profile_id
        GROUP BY skins.id
        ORDER BY skins.views_count DESC, skins.likes_count DESC, skins.id DESC
    SQL;

    try {
        $rows = $pdo->query($sql)->fetchAll();
    } catch (PDOException $exception) {
        $error = $exception->getMessage();
        return $fallbackSkins;
    }

    if ($rows === []) {
        return $fallbackSkins;
    }

    return array_map(static function (array $row): array {
        $textureHash = (string) ($row['texture_hash'] ?? '');
        $variant = ucfirst(strtolower((string) $row['variant']));
        $profiles = array_values(array_filter(explode(',', (string) ($row['profile_names'] ?? ''))));

        return [
            'slug' => (string) $row['slug'],
            'name' => (string) $row['skin_name'],
            'uuid' => (string) $row['uuid'],
            'owner' => (string) $row['owner_name'],
            'likes' => (int) $row['likes_count'],
            'views' => (int) $row['views_count'],
            'variant' => $variant,
            'tags' => ['Database', $variant],
            'profiles' => $profiles === [] ? [(string) $row['owner_name']] : $profiles,
            'description' => (string) $row['description'],
            'accent' => accent_from_hash($textureHash !== '' ? $textureHash : (string) $row['uuid']),
            'texture_hash' => $textureHash,
        ];
    }, $rows);
}

function persist_search_result(?PDO $pdo, array $skin, ?string &$error = null): bool
{
    if ($pdo === null) {
        return false;
    }

    $profiles = $skin['profiles'] ?? [];
    if ($profiles === []) {
        $error = 'No profiles were provided for persistence.';
        return false;
    }

    try {
        $pdo->beginTransaction();

        $profileName = (string) $profiles[0];
        $profileStatement = $pdo->prepare(
            'INSERT INTO profiles (profile_name, uuid) VALUES (:profile_name, :uuid)
             ON DUPLICATE KEY UPDATE uuid = VALUES(uuid), searched_at = CURRENT_TIMESTAMP'
        );
        $profileStatement->execute([
            'profile_name' => $profileName,
            'uuid' => (string) $skin['uuid'],
        ]);

        $profileIdStatement = $pdo->prepare('SELECT id FROM profiles WHERE profile_name = :profile_name LIMIT 1');
        $profileIdStatement->execute(['profile_name' => $profileName]);
        $profileId = (int) $profileIdStatement->fetchColumn();

        $skinStatement = $pdo->prepare(
            'INSERT INTO skins (
                slug,
                texture_hash,
                uuid,
                skin_name,
                owner_name,
                variant,
                description,
                likes_count,
                views_count
             ) VALUES (
                :slug,
                :texture_hash,
                :uuid,
                :skin_name,
                :owner_name,
                :variant,
                :description,
                :likes_count,
                :views_count
             )
             ON DUPLICATE KEY UPDATE
                uuid = VALUES(uuid),
                owner_name = VALUES(owner_name),
                variant = VALUES(variant),
                description = VALUES(description),
                views_count = views_count + 1'
        );
        $skinStatement->execute([
            'slug' => (string) $skin['slug'],
            'texture_hash' => (string) ($skin['texture_hash'] ?? ''),
            'uuid' => (string) $skin['uuid'],
            'skin_name' => (string) $skin['name'],
            'owner_name' => (string) $skin['owner'],
            'variant' => strtolower((string) $skin['variant']),
            'description' => (string) $skin['description'],
            'likes_count' => (int) $skin['likes'],
            'views_count' => max(1, (int) $skin['views']),
        ]);

        $skinLookup = $pdo->prepare('SELECT id FROM skins WHERE texture_hash = :texture_hash LIMIT 1');
        $skinLookup->execute([
            'texture_hash' => (string) ($skin['texture_hash'] ?? ''),
        ]);
        $skinId = (int) $skinLookup->fetchColumn();

        if ($profileId > 0 && $skinId > 0) {
            $linkStatement = $pdo->prepare(
                'INSERT IGNORE INTO skin_profiles (skin_id, profile_id) VALUES (:skin_id, :profile_id)'
            );
            $linkStatement->execute([
                'skin_id' => $skinId,
                'profile_id' => $profileId,
            ]);
        }

        $pdo->commit();
        return true;
    } catch (PDOException $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = $exception->getMessage();
        return false;
    }
}

function fetch_mojang_skin(string $query, ?string &$error = null): ?array
{
    $query = trim($query);
    if ($query === '') {
        $error = 'Search cannot be empty.';
        return null;
    }

    $profile = resolve_profile_query($query, $error);
    if ($profile === null) {
        return null;
    }

    $textureInfo = resolve_texture_data((string) $profile['id'], $error);
    if ($textureInfo === null) {
        return null;
    }

    $variant = $textureInfo['variant'];
    $textureHash = $textureInfo['texture_hash'];

    return [
        'slug' => slugify((string) $profile['name']) . '-' . substr($textureHash, 0, 6),
        'name' => (string) $profile['name'],
        'uuid' => (string) $profile['id'],
        'owner' => (string) $profile['name'],
        'likes' => random_int(60, 320),
        'views' => random_int(800, 4200),
        'variant' => $variant,
        'tags' => ['Live Search', 'Mojang API', $variant],
        'profiles' => [(string) $profile['name']],
        'description' => 'Imported live from Mojang based on the latest nickname/UUID search and ready to be persisted in MySQL.',
        'accent' => accent_from_hash($textureHash),
        'texture_hash' => $textureHash,
    ];
}

function resolve_profile_query(string $query, ?string &$error = null): ?array
{
    $normalizedUuid = normalize_uuid($query);

    if ($normalizedUuid !== null) {
        $sessionResponse = http_get_json('https://sessionserver.mojang.com/session/minecraft/profile/' . $normalizedUuid);
        if ($sessionResponse['error'] !== null) {
            $error = $sessionResponse['error'];
            return null;
        }
        if ($sessionResponse['status'] >= 400 || !is_array($sessionResponse['data'])) {
            $error = 'No Mojang profile was found for that UUID.';
            return null;
        }

        return [
            'id' => (string) ($sessionResponse['data']['id'] ?? $normalizedUuid),
            'name' => (string) ($sessionResponse['data']['name'] ?? $query),
        ];
    }

    $lookupResponse = http_get_json('https://api.mojang.com/users/profiles/minecraft/' . rawurlencode($query));
    if ($lookupResponse['error'] !== null) {
        $error = $lookupResponse['error'];
        return null;
    }
    if ($lookupResponse['status'] === 204 || !is_array($lookupResponse['data'])) {
        $error = 'No Mojang profile was found for that nickname.';
        return null;
    }

    return [
        'id' => (string) ($lookupResponse['data']['id'] ?? ''),
        'name' => (string) ($lookupResponse['data']['name'] ?? $query),
    ];
}

function resolve_texture_data(string $uuid, ?string &$error = null): ?array
{
    $sessionResponse = http_get_json('https://sessionserver.mojang.com/session/minecraft/profile/' . $uuid);
    if ($sessionResponse['error'] !== null) {
        $error = $sessionResponse['error'];
        return null;
    }
    if ($sessionResponse['status'] >= 400 || !is_array($sessionResponse['data'])) {
        $error = 'Could not retrieve session texture data from Mojang.';
        return null;
    }

    $properties = $sessionResponse['data']['properties'] ?? [];
    if (!is_array($properties)) {
        $error = 'Mojang did not return texture properties for this profile.';
        return null;
    }

    foreach ($properties as $property) {
        if (($property['name'] ?? '') !== 'textures' || !isset($property['value'])) {
            continue;
        }

        $decoded = base64_decode((string) $property['value'], true);
        if ($decoded === false) {
            $error = 'The textures payload from Mojang could not be decoded.';
            return null;
        }

        try {
            $texturePayload = json_decode($decoded, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            $error = $exception->getMessage();
            return null;
        }

        $skinUrl = (string) ($texturePayload['textures']['SKIN']['url'] ?? '');
        if ($skinUrl === '') {
            $error = 'No skin URL was returned for this profile.';
            return null;
        }

        $metadataModel = strtolower((string) ($texturePayload['textures']['SKIN']['metadata']['model'] ?? 'classic'));
        $variant = $metadataModel === 'slim' ? 'Slim' : 'Classic';

        return [
            'variant' => $variant,
            'texture_hash' => basename(parse_url($skinUrl, PHP_URL_PATH) ?: $skinUrl),
        ];
    }

    $error = 'No texture property was found in the Mojang session payload.';
    return null;
}

function http_get_json(string $url): array
{
    if (function_exists('curl_init')) {
        $handle = curl_init($url);
        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 12,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ]);

        $body = curl_exec($handle);
        $curlError = curl_error($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        curl_close($handle);

        if ($body === false) {
            return ['status' => $status, 'data' => null, 'error' => $curlError !== '' ? $curlError : 'Network request failed.'];
        }

        if ($status === 204 || trim($body) === '') {
            return ['status' => $status, 'data' => null, 'error' => null];
        }

        try {
            return [
                'status' => $status,
                'data' => json_decode($body, true, 512, JSON_THROW_ON_ERROR),
                'error' => null,
            ];
        } catch (JsonException $exception) {
            return ['status' => $status, 'data' => null, 'error' => $exception->getMessage()];
        }
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 12,
            'ignore_errors' => true,
            'header' => "Accept: application/json\r\n",
        ],
    ]);

    $body = @file_get_contents($url, false, $context);
    $headers = isset($http_response_header) && is_array($http_response_header) ? $http_response_header : [];
    $status = extract_http_status($headers);

    if ($body === false) {
        return ['status' => $status, 'data' => null, 'error' => 'Network request failed.'];
    }

    if ($status === 204 || trim($body) === '') {
        return ['status' => $status, 'data' => null, 'error' => null];
    }

    try {
        return [
            'status' => $status,
            'data' => json_decode($body, true, 512, JSON_THROW_ON_ERROR),
            'error' => null,
        ];
    } catch (JsonException $exception) {
        return ['status' => $status, 'data' => null, 'error' => $exception->getMessage()];
    }
}

function extract_http_status(array $headers): int
{
    foreach ($headers as $header) {
        if (preg_match('/HTTP\/\d+(?:\.\d+)?\s+(\d+)/i', $header, $matches) === 1) {
            return (int) $matches[1];
        }
    }

    return 0;
}

function normalize_uuid(string $value): ?string
{
    $normalized = strtolower(str_replace('-', '', trim($value)));
    if (preg_match('/^[a-f0-9]{32}$/', $normalized) !== 1) {
        return null;
    }

    return $normalized;
}

function slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    return trim($value, '-');
}

function accent_from_hash(string $hash): string
{
    $seed = substr(hash('sha256', $hash), 0, 6);
    $red = max(90, hexdec(substr($seed, 0, 2)));
    $green = max(110, hexdec(substr($seed, 2, 2)));
    $blue = max(130, hexdec(substr($seed, 4, 2)));

    return sprintf('#%02x%02x%02x', $red, $green, $blue);
}
