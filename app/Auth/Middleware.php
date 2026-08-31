<?php

/**
 * Session-backed auth guard. Other modules (Posts, Categories, Comments,
 * Media) call AuthMiddleware::requireUser() / ::requireAdmin() at the top
 * of their Author/Admin route handlers — they never touch $_SESSION or
 * blog_users directly.
 */
class AuthMiddleware
{
    private const SESSION_KEY = 'auth_user_id';

    /** Start an authenticated session for this user. Called after credentials are verified. */
    public static function login(array $userRow): void
    {
        // Regenerate first (prevents session fixation), then store identity.
        Session::regenerate();
        Session::set(self::SESSION_KEY, (int) $userRow['id']);
    }

    public static function logout(): void
    {
        Session::destroy();
    }

    /** Returns the current user, or null if not logged in / account no longer active. */
    public static function currentUser(): ?AuthUser
    {
        $id = Session::get(self::SESSION_KEY);

        if ($id === null) {
            return null;
        }

        $row = AuthRepository::findById((int) $id);

        if ($row === null || $row['status'] !== 'active') {
            return null;
        }

        return AuthUser::fromRow($row);
    }

    /** Require any logged-in user (author or admin). Halts the request with 401 if absent. */
    public static function requireUser(): AuthUser
    {
        $user = self::currentUser();

        if ($user === null) {
            Response::unauthorized('Login required.');
            exit;
        }

        return $user;
    }

    /** Require an admin. Halts the request with 401/403 if not an authenticated admin. */
    public static function requireAdmin(): AuthUser
    {
        $user = self::requireUser();

        if (!$user->isAdmin()) {
            Response::forbidden('Admin access required.');
            exit;
        }

        return $user;
    }
}
