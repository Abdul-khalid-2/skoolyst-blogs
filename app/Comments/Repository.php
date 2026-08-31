<?php

/**
 * Data access for blog_comments.
 */
class CommentRepository
{
    public static function approvedForPost(int $postId): array
    {
        return Database::select(
            "SELECT * FROM blog_comments WHERE post_id = ? AND status = 'approved' ORDER BY created_at ASC",
            [$postId]
        );
    }

    public static function paginated(int $page, int $perPage, ?string $status): array
    {
        $where = [];
        $params = [];

        if ($status !== null && $status !== '') {
            $where[] = 'c.status = ?';
            $params[] = $status;
        }

        $whereSql = $where === [] ? '1=1' : implode(' AND ', $where);
        $offset = max(0, ($page - 1) * $perPage);

        $rows = Database::select(
            "SELECT c.*, p.title AS post_title
             FROM blog_comments c
             LEFT JOIN blog_posts p ON p.id = c.post_id
             WHERE {$whereSql}
             ORDER BY c.created_at DESC
             LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );

        $total = Database::selectOne("SELECT COUNT(*) AS c FROM blog_comments c WHERE {$whereSql}", $params);

        return ['rows' => $rows, 'total' => (int) ($total['c'] ?? 0)];
    }

    public static function findById(int $id): ?array
    {
        return Database::selectOne(
            'SELECT c.*, p.title AS post_title FROM blog_comments c LEFT JOIN blog_posts p ON p.id = c.post_id WHERE c.id = ? LIMIT 1',
            [$id]
        );
    }

    /** Basic double-submit guard: same email on the same post within the window. Real IP-based rate limiting is Section 6's job once built. */
    public static function recentDuplicate(int $postId, string $email, int $windowSeconds): bool
    {
        $row = Database::selectOne(
            "SELECT id FROM blog_comments
             WHERE post_id = ? AND author_email = ? AND created_at > (NOW() - INTERVAL ? SECOND)
             LIMIT 1",
            [$postId, $email, $windowSeconds]
        );

        return $row !== null;
    }

    public static function create(array $data): int
    {
        Database::execute(
            'INSERT INTO blog_comments (post_id, author_name, author_email, body, status) VALUES (?, ?, ?, ?, ?)',
            [$data['post_id'], $data['author_name'], $data['author_email'], $data['body'], $data['status']]
        );

        return (int) Database::lastInsertId();
    }

    public static function updateStatus(int $id, string $status): void
    {
        Database::execute('UPDATE blog_comments SET status = ? WHERE id = ?', [$status, $id]);
    }

    public static function delete(int $id): void
    {
        Database::execute('DELETE FROM blog_comments WHERE id = ?', [$id]);
    }
}
