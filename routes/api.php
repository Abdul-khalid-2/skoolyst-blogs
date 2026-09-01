<?php

/**
 * API v1 routes.
 * Module-specific route files (Posts, Categories, Comments, Media, Auth)
 * get required and merged in here as each module lands — see Section 8/9
 * of README.md. Only a health check exists so far to prove the routing
 * layer + front controller work end-to-end.
 *
 * @var Router $router
 */

$router->get('/health', function (Request $request) {
    Response::success([
        'status' => 'ok',
        'app' => Config::get('app.name'),
        'time' => date('c'),
    ]);
});

require_once __DIR__ . '/api/auth.php';
require_once __DIR__ . '/api/categories.php';
require_once __DIR__ . '/api/posts.php';
require_once __DIR__ . '/api/comments.php';
require_once __DIR__ . '/api/media.php';
