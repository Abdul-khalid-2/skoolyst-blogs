<?php

/**
 * Authors module routes.
 * Public, read-only, no admin/author write side — author accounts are
 * managed through Auth's own seed/login flow, not through this module.
 * Exists to give public pages (about.html's team section) a real
 * endpoint instead of reading MOCK_AUTHORS out of mock-data.js.
 *
 * @var Router $router
 */

require_once __DIR__ . '/../../app/Authors/Model.php';
require_once __DIR__ . '/../../app/Authors/Repository.php';
require_once __DIR__ . '/../../app/Authors/Controller.php';

// Public
$router->get('/authors', ['AuthorController', 'index']);
$router->get('/authors/{id}', ['AuthorController', 'show']);
