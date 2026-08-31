<?php

/**
 * Thin wrapper around PHP's native session, using this app's own,
 * uniquely-named session cookie (config('app.session_name')) so it never
 * collides with ads.skoolyst.com or teachers.skoolyst.com sessions on the
 * same domain family.
 */
class Session
{
    private static bool $started = false;

    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        session_name(Config::get('app.session_name', 'blog_skoolyst_session'));

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => Config::get('app.env') === 'production',
        ]);

        session_start();
        self::$started = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    /** Regenerate the session ID (call on login to prevent session fixation). */
    public static function regenerate(): void
    {
        self::start();
        session_regenerate_id(true);
    }

    /** Fully destroy the session (call on logout). */
    public static function destroy(): void
    {
        self::start();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
    }
}
