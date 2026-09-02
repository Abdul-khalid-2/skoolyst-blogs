<?php
/**
 * LOCAL DEV TESTING ONLY — mirrors .htaccess's exact logic so that
 * `php -S host:port -t . bin/dev-router.php` behaves the same way
 * production Apache + .htaccess does:
 *   1. Real files/directories are served as-is.
 *   2. Requests under /api/v1/... go through router.php.
 *   3. Everything else is a 404.
 *
 * Not used in production — Apache reads .htaccess directly there.
 * This file exists purely so `php -S` testing doesn't need a real
 * Apache instance to verify routing/rewrite behavior.
 */

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . '/../' . ltrim($path, '/');

if ($path !== '/' && (is_file($file) || is_dir($file))) {
    return false; // let the built-in server serve the real file/dir as-is
}

if (str_starts_with($path, '/api/v1/')) {
    chdir(__DIR__ . '/..');
    require __DIR__ . '/../router.php';
    return true;
}

http_response_code(404);
echo 'Not Found';
