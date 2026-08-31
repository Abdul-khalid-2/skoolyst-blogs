<?php

/**
 * Data access for blog_posts. Every SELECT joins blog_users/blog_categories
 * for display fields (author name, category name/slug/color) so callers
 * never need a second round trip — same reasoning as CategoryRepository
 * joining post_count. All non-admin-facing queries exclude soft-deleted
 * rows (`deleted_at IS NULL`); admin queries do too for now since there's
 * no restore flow yet.
 */
class PostRepository
{
    private const SELECT = '
        SELECT p.*, u.name AS author_name,
               c.name AS category_name, c.slug AS category_slug, c.color AS category_color
        FROM blog_posts p
        LEFT JOIN blog_users u ON u.id = p.author_id
        LEFT JOIN blog_categories c ON c.id = p.category_id
    ';

    /** Public: published posts only, optional category slug filter + search, paginated. */
    public static function publishedPaginated(int $page, int $perPage, ?string $categorySlug, ?string $search): array
    {
        $where = ['p.deleted_at IS NULL', "p.status = 'published'"];
        $params = [];

        if ($categorySlug !== null && $categorySlug !== '') {
            $where[] = 'c.slug = ?';
            $params[] = $categorySlug;
        }

        if ($search !== null && $search !== '') {
            $where[] = '(p.title LIKE ? OR p.excerpt LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $whereSql = implode(' AND ', $where);
        $offset = max(0, ($page - 1) * $perPage);

        $rows = Database::select(
            self::SELECT . " WHERE {$whereSql} ORDER BY p.published_date DESC LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );

        $total = Database::selectOne(
            "SELECT COUNT(*) AS c FROM blog_posts p LEFT JOIN blog_categories c ON c.id = p.category_id WHERE {$whereSql}",
            $params
        );

        return ['rows' => $rows, 'total' => (int) ($total['c'] ?? 0)];
    }

    public static function findPublishedById(int $id): ?array
    {
        return Database::selectOne(
            self::SELECT . " WHERE p.id = ? AND p.status = 'published' AND p.deleted_at IS NULL LIMIT 1",
            [$id]
        );
    }

    /** No status/ownership filter — for author/admin use once they've already been authorized. */
    public static function findById(int $id): ?array
    {
        return Database::selectOne(self::SELECT . ' WHERE p.id = ? AND p.deleted_at IS NULL LIMIT 1', [$id]);
    }

    public static function byAuthorPaginated(int $authorId, int $page, int $perPage): array
    {
        $offset = max(0, ($page - 1) * $perPage);

        $rows = Database::select(
            self::SELECT . ' WHERE p.author_id = ? AND p.deleted_at IS NULL ORDER BY p.created_at DESC LIMIT ? OFFSET ?',
            [$authorId, $perPage, $offset]
        );
        $total = Database::selectOne(
            'SELECT COUNT(*) AS c FROM blog_posts WHERE author_id = ? AND deleted_at IS NULL',
            [$authorId]
        );

        return ['rows' => $rows, 'total' => (int) ($total['c'] ?? 0)];
    }

    public static function allPaginated(int $page, int $perPage, ?string $status): array
    {
        $where = ['p.deleted_at IS NULL'];
        $params = [];

        if ($status !== null && $status !== '') {
            $where[] = 'p.status = ?';
            $params[] = $status;
        }

        $whereSql = implode(' AND ', $where);
        $offset = max(0, ($page - 1) * $perPage);

        $rows = Database::select(
            self::SELECT . " WHERE {$whereSql} ORDER BY p.created_at DESC LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );
        $total = Database::selectOne("SELECT COUNT(*) AS c FROM blog_posts p WHERE {$whereSql}", $params);

        return ['rows' => $rows, 'total' => (int) ($total['c'] ?? 0)];
    }

    public static function slugExists(string $slug, ?int $exceptId = null): bool
    {
        if ($exceptId !== null) {
            $row = Database::selectOne('SELECT id FROM blog_posts WHERE slug = ? AND id != ? LIMIT 1', [$slug, $exceptId]);
        } else {
            $row = Database::selectOne('SELECT id FROM blog_posts WHERE slug = ? LIMIT 1', [$slug]);
        }

        return $row !== null;
    }

    public static function create(array $data): int
    {
        Database::execute(
            'INSERT INTO blog_posts
                (title, slug, excerpt, body, cover_image, status, author_id, category_id,
                 published_date, seo_title, seo_description)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['title'], $data['slug'], $data['excerpt'], $data['body'], $data['cover_image'],
                $data['status'], $data['author_id'], $data['category_id'],
                $data['published_date'], $data['seo_title'], $data['seo_description'],
            ]
        );

        return (int) Database::lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        Database::execute(
            'UPDATE blog_posts SET
                title = ?, slug = ?, excerpt = ?, body = ?, cover_image = ?, status = ?,
                category_id = ?, published_date = ?, seo_title = ?, seo_description = ?
             WHERE id = ?',
            [
                $data['title'], $data['slug'], $data['excerpt'], $data['body'], $data['cover_image'],
                $data['status'], $data['category_id'], $data['published_date'],
                $data['seo_title'], $data['seo_description'], $id,
            ]
        );
    }

    /** Admin-only: reassign a post to a different author. */
    public static function updateAuthor(int $id, int $authorId): void
    {
        Database::execute('UPDATE blog_posts SET author_id = ? WHERE id = ?', [$authorId, $id]);
    }

    public static function softDelete(int $id): void
    {
        Database::execute('UPDATE blog_posts SET deleted_at = NOW() WHERE id = ?', [$id]);
    }

    public static function incrementViews(int $id): void
    {
        Database::execute('UPDATE blog_posts SET views = views + 1 WHERE id = ? AND deleted_at IS NULL', [$id]);
    }
}
