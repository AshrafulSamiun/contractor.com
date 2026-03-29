<?php
$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

$publicPath = __DIR__ . '/public' . $uri;
$isAppRoute = str_starts_with($uri, '/app');
$isAppAsset = str_starts_with($uri, '/app/assets/')
    || $uri === '/app/manifest.json'
    || $uri === '/app/index.php';

if ($uri !== '/' && is_file($publicPath) && !($isAppRoute && !$isAppAsset)) {
    return false;
}

require_once __DIR__ . '/public/index.php';
