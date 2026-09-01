<?php

/**
 * Media module routes.
 * The general library lives at /admin/media (Section 8: "Admin: full CRUD
 * on ... /admin/media"). A post's own cover-image upload is registered in
 * routes/api/posts.php (POST /author/posts/{id}/image, Section 8's Author
 * API) since it's really a Posts action — it just happens to reuse this
 * module's Upload/MediaRepository plumbing.
 *
 * @var Router $router
 */

require_once __DIR__ . '/../../core/Upload.php';
require_once __DIR__ . '/../../app/Media/Model.php';
require_once __DIR__ . '/../../app/Media/Repository.php';
require_once __DIR__ . '/../../app/Media/Controller.php';

$router->get('/admin/media', ['MediaController', 'index']);
$router->post('/admin/media', ['MediaController', 'store']);
$router->patch('/admin/media/{id}', ['MediaController', 'update']);
$router->delete('/admin/media/{id}', ['MediaController', 'destroy']);
