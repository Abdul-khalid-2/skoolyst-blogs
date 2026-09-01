<?php

/**
 * Public read access to blog_users for the "meet the team" listing.
 * Deliberately scoped to role='author' AND status='active' — admin
 * accounts and suspended authors never appear on a public page.
 */
class AuthorRepository
{
    public static function allActive(): array
    {
        return Database::select(
            "SELECT id, name, avatar_url, bio
             FROM blog_users
             WHERE role = 'author' AND status = 'active'
             ORDER BY name ASC"
        );
    }

    public static function findActiveById(int $id): ?array
    {
        return Database::selectOne(
            "SELECT id, name, avatar_url, bio
             FROM blog_users
             WHERE id = ? AND role = 'author' AND status = 'active'
             LIMIT 1",
            [$id]
        );
    }
}
