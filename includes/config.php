<?php

declare(strict_types=1);

load_demo_environment(__DIR__ . '/../.env');

function mineavatar_database_settings(): array
{
    return [
        'host' => getenv('MINEAVATAR_DB_HOST') ?: '',
        'port' => getenv('MINEAVATAR_DB_PORT') ?: '3306',
        'database' => getenv('MINEAVATAR_DB_NAME') ?: '',
        'username' => getenv('MINEAVATAR_DB_USER') ?: '',
        'password' => getenv('MINEAVATAR_DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ];
}

function load_demo_environment(string $path): void
{
    if (!is_file($path) || !is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '#') || !str_contains($trimmed, '=')) {
            continue;
        }

        [$name, $value] = explode('=', $trimmed, 2);
        $name = trim($name);
        $value = trim($value);

        if ($name === '' || getenv($name) !== false) {
            continue;
        }

        putenv($name . '=' . $value);
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}
