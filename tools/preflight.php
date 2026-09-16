<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];
$phpFiles = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));

foreach ($iterator as $file) {
    if (! $file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getPathname();
    $phpFiles[] = $path;
    $output = [];
    $status = 0;
    exec('php -l ' . escapeshellarg($path) . ' 2>&1', $output, $status);
    if ($status !== 0) {
        $errors[] = 'PHP lint failed: ' . $path . ' :: ' . implode(' ', $output);
    }

    if (realpath($path) === realpath(__FILE__)) {
        continue;
    }

    $source = (string) file_get_contents($path);
    foreach (['$wpdb->', 'wp_remote_get(', 'wp_remote_post(', 'switch_to_blog('] as $needle) {
        if (str_contains($source, $needle)) {
            $errors[] = 'Business/infrastructure primitive forbidden in theme: ' . $needle . ' :: ' . $path;
        }
    }
}

$style = (string) file_get_contents($root . '/style.css');
if (! preg_match('/^Template:\s*twentytwentyfive\s*$/mi', $style)) {
    $errors[] = 'Theme must declare Twenty Twenty-Five as parent.';
}

if (! is_file($root . '/templates/portal.php')) {
    $errors[] = 'Missing Portal application template.';
}

foreach (['assets/js/pwa.js', 'assets/js/portal.js', 'assets/icons/icon-192.png', 'assets/icons/icon-512.png', 'templates/portal/parts/login.php'] as $requiredFile) {
    if (! is_file($root . '/' . $requiredFile)) {
        $errors[] = 'Missing Portal UI/PWA asset: ' . $requiredFile;
    }
}

$themeJsonPath = $root . '/theme.json';
$themeJson = is_file($themeJsonPath) ? json_decode((string) file_get_contents($themeJsonPath), true) : null;
if (! is_array($themeJson)) {
    $errors[] = 'theme.json must be valid JSON.';
} else {
    if (($themeJson['settings']['appearanceTools'] ?? false) !== true) {
        $errors[] = 'theme.json must enable appearanceTools.';
    }
    if (empty($themeJson['settings']['color']['palette'])) {
        $errors[] = 'theme.json must expose a native WordPress color palette.';
    }
    if (empty($themeJson['settings']['typography']['fontFamilies'])) {
        $errors[] = 'theme.json must expose native WordPress typography settings.';
    }
}

$css = (string) file_get_contents($root . '/assets/css/portal.css');
if (! str_contains($css, '--wp--preset--color--accent')) {
    $errors[] = 'Portal CSS must consume WordPress Global Styles variables.';
}

echo "RC Portal Theme 0.1.0-alpha3 — Preflight\n";
echo "==========================================\n";
echo 'PHP lint: ' . ($errors === [] ? 'GREEN' : 'CHECK') . ' (' . count($phpFiles) . " files)\n";
echo "Parent theme: Twenty Twenty-Five expected\n";
echo "WordPress Global Styles: enabled expected\n";
echo "Direct database access: 0 expected\n";
echo "Direct HTTP/ERP calls: 0 expected\n";
echo "Multisite cross-site calls: 0 expected\n";
echo "PWA caches business data: 0 expected\n\n";

if ($errors !== []) {
    foreach ($errors as $error) {
        echo '[FAIL] ' . $error . "\n";
    }
    echo "\nRESULT: RED\n";
    exit(1);
}

echo "RESULT: GREEN\n";
