<?php

/**
 * Posts module routes.
 * Public reads at /posts (Section 8); author's own-post CRUD at /author/posts;
 * admin full CRUD (any author) at /admin/posts. Ownership/role checks happen
 * inside PostController via AuthMiddleware, same pattern as Categories/Auth.
 *
 * @var Router $router
 */

require __DIR__ . '/../../app/Posts/Model.php';
require __DIR__ . '/../../app/Posts/Repository.php';
require __DIR__ . '/../../app/Posts/Controller.php';

// Public
$router->get('/posts', ['PostController', 'index']);
$router->get('/posts/{id}', ['PostController', 'show']);
$router->post('/posts/{id}/view', ['PostController', 'recordView']);

// Author (own posts only)
$router->get('/author/posts', ['PostController', 'authorIndex']);
$router->post('/author/posts', ['PostController', 'authorStore']);
$router->patch('/author/posts/{id}', ['PostController', 'authorUpdate']);
$router->delete('/author/posts/{id}', ['PostController', 'authorDestroy']);

// Admin (any post, any author)
$router->get('/admin/posts', ['PostController', 'adminIndex']);
$router->get('/admin/posts/{id}', ['PostController', 'adminShow']);
$router->post('/admin/posts', ['PostController', 'adminStore']);
$router->patch('/admin/posts/{id}', ['PostController', 'adminUpdate']);
$router->delete('/admin/posts/{id}', ['PostController', 'adminDestroy']);
