<?php

/**
 * Auth module routes — /api/v1/auth/...
 *
 * @var Router $router
 */

require_once __DIR__ . '/../../app/Auth/Model.php';
require_once __DIR__ . '/../../app/Auth/Repository.php';
require_once __DIR__ . '/../../app/Auth/Middleware.php';
require_once __DIR__ . '/../../app/Auth/Controller.php';

$router->post('/auth/login', ['AuthController', 'login']);
$router->post('/auth/logout', ['AuthController', 'logout']);
$router->get('/auth/me', ['AuthController', 'me']);
