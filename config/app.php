<?php

return [
    'name' => Env::get('APP_NAME', 'Skoolyst Blog'),
    'url' => Env::get('APP_URL', 'https://blog.skoolyst.com'),
    'env' => Env::get('APP_ENV', 'production'),
    'debug' => Env::get('APP_DEBUG', false),
    'timezone' => Env::get('APP_TIMEZONE', 'Asia/Karachi'),

    // Session name MUST be unique to this app so it never collides with
    // ads.skoolyst.com or teachers.skoolyst.com sessions on the same domain family.
    'session_name' => Env::get('SESSION_NAME', 'blog_skoolyst_session'),

    'upload_path' => Env::get('UPLOAD_PATH', __DIR__ . '/../public/uploads/media'),
    'upload_max_size' => (int) Env::get('UPLOAD_MAX_SIZE', 5 * 1024 * 1024), // bytes
    'upload_allowed_mimes' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
];
