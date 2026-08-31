<?php

/**
 * Data access for blog_categories. Post counts are joined in here (not
 * computed in the Posts module) since only the dashboard categories list
 * needs them and it's a single cheap GROUP BY — no reason to make Posts
 * aware of Categories just to expose a count.
 */
class CategoryRepository
{
    public static function all(): array
    {
        return Database::select(
            'SELECT c.*,
                    (SELECT COUNT(*) FROM blog_posts p
                     WHERE p.category_id = c.id AND p.deleted_at IS NULL) AS post_count
             FROM blog_categories c
             ORDER BY c.name ASC'
        );
    }

    public static function findById(int $id): ?array
    {
        return Database::selectOne('SELECT * FROM blog_categories WHERE id = ? LIMIT 1', [$id]);
    }

    public static function findBySlug(string $slug): ?array
    {
        return Database::selectOne('SELECT * FROM blog_categories WHERE slug = ? LIMIT 1', [$slug]);
    }

    public static function slugExists(string $slug, ?int $exceptId = null): bool
    {
        if ($exceptId !== null) {
            $row = Database::selectOne(
                'SELECT id FROM blog_categories WHERE slug = ? AND id != ? LIMIT 1',
                [$slug, $exceptId]
            );
        } else {
            $row = Database::selectOne('SELECT id FROM blog_categories WHERE slug = ? LIMIT 1', [$slug]);
        }

        return $row !== null;
    }

    public static function create(array $data): int
    {
        Database::execute(
            'INSERT INTO blog_categories (name, slug, description, color) VALUES (?, ?, ?, ?)',
            [$data['name'], $data['slug'], $data['description'], $data['color']]
        );

        return (int) Database::lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        Database::execute(
            'UPDATE blog_categories SET name = ?, slug = ?, description = ?, color = ? WHERE id = ?',
            [$data['name'], $data['slug'], $data['description'], $data['color'], $id]
        );
    }

    public static function delete(int $id): void
    {
        Database::execute('DELETE FROM blog_categories WHERE id = ?', [$id]);
    }

    /** Number of non-deleted posts still referencing this category — mirrors the delete guard in dashboard.js. */
    public static function countPostsUsing(int $id): int
    {
        $row = Database::selectOne(
            'SELECT COUNT(*) AS c FROM blog_posts WHERE category_id = ? AND deleted_at IS NULL',
            [$id]
        );

        return (int) ($row['c'] ?? 0);
    }
}
