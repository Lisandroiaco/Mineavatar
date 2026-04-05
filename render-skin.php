<?php

declare(strict_types=1);

$seed = isset($_GET['seed']) ? (string) $_GET['seed'] : 'mineavatar';
$size = isset($_GET['size']) ? max(80, min(520, (int) $_GET['size'])) : 180;
$mode = isset($_GET['mode']) ? (string) $_GET['mode'] : 'body';

$palette = palette_from_seed($seed);

header('Content-Type: image/svg+xml; charset=UTF-8');

echo $mode === 'avatar'
    ? render_avatar_svg($size, $palette)
    : render_body_svg($size, $palette);

function render_body_svg(int $size, array $palette): string
{
    $w = 44.0;
    $h = 22.0;
    $canvas = 260.0;
    $scale = $size / $canvas;

    $parts = [];
    $parts[] = prism(108, 42, 0, 44, 44, 20, $palette['skin'], adjust_color($palette['skin'], 0.16), adjust_color($palette['skin'], -0.18));
    $parts[] = prism(110, 42, 1, 44, 8, 20, $palette['hair'], adjust_color($palette['hair'], 0.18), adjust_color($palette['hair'], -0.16));
    $parts[] = prism(111, 88, 2, 40, 54, 18, $palette['shirt'], adjust_color($palette['shirt'], 0.15), adjust_color($palette['shirt'], -0.18));
    $parts[] = prism(88, 92, 4, 16, 52, 16, $palette['shirtDark'], adjust_color($palette['shirtDark'], 0.12), adjust_color($palette['shirtDark'], -0.14));
    $parts[] = prism(156, 96, 3, 16, 48, 16, $palette['shirtDark'], adjust_color($palette['shirtDark'], 0.16), adjust_color($palette['shirtDark'], -0.16));
    $parts[] = prism(112, 144, 4, 17, 60, 18, $palette['pants'], adjust_color($palette['pants'], 0.14), adjust_color($palette['pants'], -0.18));
    $parts[] = prism(131, 146, 2, 17, 58, 18, adjust_color($palette['pants'], 0.06), adjust_color($palette['pants'], 0.2), adjust_color($palette['pants'], -0.12));
    $parts[] = prism(112, 204, 6, 17, 8, 18, $palette['boots'], adjust_color($palette['boots'], 0.1), adjust_color($palette['boots'], -0.2));
    $parts[] = prism(131, 204, 4, 17, 8, 18, adjust_color($palette['boots'], 0.02), adjust_color($palette['boots'], 0.12), adjust_color($palette['boots'], -0.18));

    usort($parts, static fn(array $a, array $b): int => $a['z'] <=> $b['z']);

    $svgParts = '';
    foreach ($parts as $part) {
        $svgParts .= $part['svg'];
    }

    [$leftEyeX, $leftEyeY] = format_point(project_point(123, 58, 0));
    [$rightEyeX, $rightEyeY] = format_point(project_point(138, 58, 0));
    [$mouthX, $mouthY] = format_point(project_point(131, 71, 0));

    $safeScale = number_format($scale, 4, '.', '');
    $eyeRadius = number_format(2.4 * $scale, 2, '.', '');

    return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$size}" height="{$size}" viewBox="0 0 {$size} {$size}" fill="none">
  <defs>
    <filter id="shadow" x="-20%" y="-20%" width="160%" height="180%">
      <feDropShadow dx="0" dy="10" stdDeviation="8" flood-color="#0f172a" flood-opacity="0.16"/>
    </filter>
  </defs>
  <g transform="scale({$safeScale})" filter="url(#shadow)">
    <ellipse cx="130" cy="232" rx="44" ry="12" fill="#0f172a" opacity="0.12"/>
    {$svgParts}
    <circle cx="{$leftEyeX}" cy="{$leftEyeY}" r="{$eyeRadius}" fill="#111827"/>
    <circle cx="{$rightEyeX}" cy="{$rightEyeY}" r="{$eyeRadius}" fill="#111827"/>
    <rect x="{$mouthX}" y="{$mouthY}" width="8" height="2.5" rx="1.25" fill="#374151" opacity="0.55"/>
  </g>
</svg>
SVG;
}

function render_avatar_svg(int $size, array $palette): string
{
    $canvas = 120.0;
    $scale = $size / $canvas;
    $headSvg = prism(26, 22, 0, 42, 42, 18, $palette['skin'], adjust_color($palette['skin'], 0.16), adjust_color($palette['skin'], -0.18))['svg'];
    $hairSvg = prism(28, 22, 1, 42, 8, 18, $palette['hair'], adjust_color($palette['hair'], 0.18), adjust_color($palette['hair'], -0.16))['svg'];
    [$leftEyeX, $leftEyeY] = format_point(project_point(40, 37, 0));
    [$rightEyeX, $rightEyeY] = format_point(project_point(54, 37, 0));
    $safeScale = number_format($scale, 4, '.', '');
    $eyeRadius = number_format(2.2 * $scale, 2, '.', '');

    return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$size}" height="{$size}" viewBox="0 0 {$size} {$size}" fill="none">
  <g transform="scale({$safeScale})">
    <ellipse cx="60" cy="94" rx="24" ry="8" fill="#0f172a" opacity="0.12"/>
    {$headSvg}
    {$hairSvg}
    <circle cx="{$leftEyeX}" cy="{$leftEyeY}" r="{$eyeRadius}" fill="#111827"/>
    <circle cx="{$rightEyeX}" cy="{$rightEyeY}" r="{$eyeRadius}" fill="#111827"/>
  </g>
</svg>
SVG;
}

function prism(float $x, float $y, int $z, float $width, float $height, float $depth, string $frontColor, string $topColor, string $sideColor): array
{
    $topLeft = project_point($x, $y, $z);
    $topRight = project_point($x + $width, $y, $z);
    $bottomLeft = project_point($x, $y + $height, $z);
    $bottomRight = project_point($x + $width, $y + $height, $z);

    $topLeftBack = project_point($x, $y, $z + $depth);
    $topRightBack = project_point($x + $width, $y, $z + $depth);
    $bottomRightBack = project_point($x + $width, $y + $height, $z + $depth);

    $front = points_to_string([$topLeft, $topRight, $bottomRight, $bottomLeft]);
    $top = points_to_string([$topLeftBack, $topRightBack, $topRight, $topLeft]);
    $side = points_to_string([$topRightBack, $bottomRightBack, $bottomRight, $topRight]);

    $svg = <<<SVG
<polygon points="{$top}" fill="{$topColor}"/>
<polygon points="{$side}" fill="{$sideColor}"/>
<polygon points="{$front}" fill="{$frontColor}"/>
SVG;

    return ['z' => $z, 'svg' => $svg];
}

function project_point(float $x, float $y, float $z): array
{
    return [
        $x + ($z * 0.52),
        $y - ($z * 0.34),
    ];
}

function points_to_string(array $points): string
{
    return implode(' ', array_map(static function (array $point): string {
        [$x, $y] = format_point($point);
        return $x . ',' . $y;
    }, $points));
}

function format_point(array $point): array
{
    return [
        number_format((float) $point[0], 2, '.', ''),
        number_format((float) $point[1], 2, '.', ''),
    ];
}

function palette_from_seed(string $seed): array
{
    $hash = hash_seed($seed);

    return [
        'skin' => '#' . mix_hex(substr($hash, 0, 6), 'e8c8a8', 0.55),
        'hair' => '#' . mix_hex(substr($hash, 6, 6), '1f2937', 0.35),
        'shirt' => '#' . mix_hex(substr($hash, 12, 6), '9cc6ff', 0.52),
        'shirtDark' => '#' . mix_hex(substr($hash, 18, 6), '3b82f6', 0.42),
        'pants' => '#' . mix_hex(substr($hash, 24, 6), '4b5563', 0.4),
        'boots' => '#' . mix_hex(substr($hash, 30, 6), '6b4f3a', 0.35),
    ];
}

function hash_seed(string $seed): string
{
    $hash = hash('sha256', $seed);

    if (strlen($hash) >= 36) {
        return $hash;
    }

    return str_pad($hash, 36, '0');
}

function mix_hex(string $hexA, string $hexB, float $ratio): string
{
    [$rA, $gA, $bA] = hex_to_rgb($hexA);
    [$rB, $gB, $bB] = hex_to_rgb($hexB);

    $r = (int) round(($rA * (1 - $ratio)) + ($rB * $ratio));
    $g = (int) round(($gA * (1 - $ratio)) + ($gB * $ratio));
    $b = (int) round(($bA * (1 - $ratio)) + ($bB * $ratio));

    return sprintf('%02x%02x%02x', $r, $g, $b);
}

function adjust_color(string $hex, float $amount): string
{
    [$r, $g, $b] = hex_to_rgb(ltrim($hex, '#'));
    if ($amount >= 0) {
        $r = (int) round($r + ((255 - $r) * $amount));
        $g = (int) round($g + ((255 - $g) * $amount));
        $b = (int) round($b + ((255 - $b) * $amount));
    } else {
        $factor = 1 + $amount;
        $r = (int) round($r * $factor);
        $g = (int) round($g * $factor);
        $b = (int) round($b * $factor);
    }

    return sprintf('#%02x%02x%02x', clamp($r), clamp($g), clamp($b));
}

function hex_to_rgb(string $hex): array
{
    $hex = ltrim($hex, '#');

    return [
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2)),
    ];
}

function clamp(int $value): int
{
    return max(0, min(255, $value));
}
