<?php

/**
 * Front controller — every request under /api/v1/... is routed here
 * by .htaccess. Boots config/env, wires up the router, and dispatches.
 */

declare(strict_types=1);

require_once __DIR__ . '/core/Env.php';
require_once __DIR__ . '/core/Config.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Request.php';
require_once __DIR__ . '/core/Response.php';
require_once __DIR__ . '/core/Validator.php';
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/core/Session.php';

Env::load(__DIR__ . '/.env');
Config::init(__DIR__ . '/config');
Session::start();

$isDebug = Env::get('APP_DEBUG', false) === true;
error_reporting($isDebug ? E_ALL : 0);
ini_set('display_errors', $isDebug ? '1' : '0');

set_exception_handler(function (Throwable $e) use ($isDebug) {
    error_log('[blog.skoolyst.com] Unhandled exception: ' . $e->getMessage());
    Response::error(
        $isDebug ? $e->getMessage() : 'Internal server error.',
        500,
        $isDebug ? ['trace' => explode("\n", $e->getTraceAsString())] : []
    );
});

$request = new Request();

// Only requests actually under /api/v1 reach the router; everything
// else (the static frontend pages) is served directly by Apache and
// never touches this file.
if (!str_starts_with($request->path, '/api/v1')) {
    Response::notFound('Not an API route.');
    exit;
}

// Strip the /api/v1 prefix so route definitions stay clean ('/posts' not '/api/v1/posts').
$trimmedPath = substr($request->path, strlen('/api/v1')) ?: '/';
$request = $request->withPath($trimmedPath);

$router = new Router();
require_once __DIR__ . '/routes/api.php';
$router->dispatch($request);
