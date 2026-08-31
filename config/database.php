<?php

return [
    'host' => Env::get('DB_HOST', '127.0.0.1'),
    'port' => Env::get('DB_PORT', '3306'),
    'name' => Env::get('DB_DATABASE', 'skoolyst_blog'),
    'user' => Env::get('DB_USERNAME', 'root'),
    'password' => Env::get('DB_PASSWORD', ''),
    'charset' => Env::get('DB_CHARSET', 'utf8mb4'),

    // Every table this app touches must live under this prefix — never
    // query or join against tables from ads.skoolyst.com or teachers.skoolyst.com.
    'table_prefix' => 'blog_',
];
