<?php

/**
 * Categories module routes.
 * Public reads live at /categories (Section 8: "Public: ... GET /categories");
 * admin writes live at /admin/categories (Section 8: "Admin: full CRUD on
 * .../admin/categories"), guarded inside CategoryController itself via
 * AuthMiddleware::requireAdmin() (same pattern as Auth module's own routes).
 *
 * @var Router $router
 */

require __DIR__ . '/../../core/Str.php';
require __DIR__ . '/../../app/Categories/Model.php';
require __DIR__ . '/../../app/Categories/Repository.php';
require __DIR__ . '/../../app/Categories/Controller.php';

// Public
$router->get('/categories', ['CategoryController', 'index']);
$router->get('/categories/{slug}', ['CategoryController', 'show']);

// Admin
$router->post('/admin/categories', ['CategoryController', 'store']);
$router->patch('/admin/categories/{id}', ['CategoryController', 'update']);
$router->delete('/admin/categories/{id}', ['CategoryController', 'destroy']);
