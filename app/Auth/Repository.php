<?php

/**
 * Data access for blog_users, scoped to what Auth needs.
 * Posts/Comments/Media modules that need author display info (name, avatar)
 * should read blog_users directly in their own repositories — this class
 * stays narrow to login/session concerns only.
 */
class AuthRepository
{
    public static function findByEmail(string $email): ?array
    {
        return Database::selectOne(
            'SELECT * FROM blog_users WHERE email = ? LIMIT 1',
            [$email]
        );
    }

    public static function findById(int $id): ?array
    {
        return Database::selectOne(
            'SELECT * FROM blog_users WHERE id = ? LIMIT 1',
            [$id]
        );
    }
}
