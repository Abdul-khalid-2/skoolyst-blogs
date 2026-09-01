<?php

/**
 * Data access for blog_media.
 */
class MediaRepository
{
    private const SELECT = '
        SELECT m.*, u.name AS uploaded_by_name
        FROM blog_media m
        LEFT JOIN blog_users u ON u.id = m.uploaded_by
    ';

    public static function paginated(int $page, int $perPage): array
    {
        $offset = max(0, ($page - 1) * $perPage);

        $rows = Database::select(
            self::SELECT . ' ORDER BY m.created_at DESC LIMIT ? OFFSET ?',
            [$perPage, $offset]
        );
        $total = Database::selectOne('SELECT COUNT(*) AS c FROM blog_media');

        return ['rows' => $rows, 'total' => (int) ($total['c'] ?? 0)];
    }

    public static function findById(int $id): ?array
    {
        return Database::selectOne(self::SELECT . ' WHERE m.id = ? LIMIT 1', [$id]);
    }

    public static function create(array $data): int
    {
        Database::execute(
            'INSERT INTO blog_media (filename, file_path, alt_text, uploaded_by) VALUES (?, ?, ?, ?)',
            [$data['filename'], $data['file_path'], $data['alt_text'], $data['uploaded_by']]
        );

        return (int) Database::lastInsertId();
    }

    public static function updateAltText(int $id, ?string $altText): void
    {
        Database::execute('UPDATE blog_media SET alt_text = ? WHERE id = ?', [$altText, $id]);
    }

    public static function delete(int $id): void
    {
        Database::execute('DELETE FROM blog_media WHERE id = ?', [$id]);
    }

    /** Posts whose cover_image still points at this file — mirrors Categories' delete guard. */
    public static function countPostsUsingCover(string $filePath): int
    {
        $row = Database::selectOne(
            'SELECT COUNT(*) AS c FROM blog_posts WHERE cover_image = ? AND deleted_at IS NULL',
            [$filePath]
        );

        return (int) ($row['c'] ?? 0);
    }
}
