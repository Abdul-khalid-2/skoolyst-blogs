<?php

/**
 * Comments module routes.
 * Submitting a comment lives under /posts/{id}/comments (Section 8's Public
 * API list) rather than its own top-level /comments; moderation lives under
 * /admin/comments, same pattern as the other admin resources.
 *
 * @var Router $router
 */

require __DIR__ . '/../../app/Comments/Model.php';
require __DIR__ . '/../../app/Comments/Repository.php';
require __DIR__ . '/../../app/Comments/Controller.php';

// Public
$router->post('/posts/{id}/comments', ['CommentController', 'store']);

// Admin
$router->get('/admin/comments', ['CommentController', 'adminIndex']);
$router->patch('/admin/comments/{id}', ['CommentController', 'updateStatus']);
$router->delete('/admin/comments/{id}', ['CommentController', 'destroy']);
