<?php

/**
 * Admin-only media library: GET/POST/PATCH/DELETE /admin/media.
 * A post's own cover image goes through PostController::uploadCoverImage()
 * (POST /author/posts/{id}/image, Section 8's Author API) instead — that
 * flow also writes a row here via MediaRepository::create() so every
 * upload, however it came in, still shows up in this one library.
 */
class MediaController
{
    private const DEFAULT_PER_PAGE = 20;
    private const MAX_PER_PAGE = 100;

    public static function index(Request $request): void
    {
        AuthMiddleware::requireAdmin();

        $page = max(1, (int) $request->input('page', 1));
        $perPage = (int) $request->input('per_page', self::DEFAULT_PER_PAGE);
        $perPage = $perPage > 0 ? min($perPage, self::MAX_PER_PAGE) : self::DEFAULT_PER_PAGE;

        $result = MediaRepository::paginated($page, $perPage);

        Response::success(
            array_map(fn (array $row) => Media::fromRow($row)->toArray(), $result['rows']),
            200,
            [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $result['total'],
                'total_pages' => (int) ceil($result['total'] / $perPage),
            ]
        );
    }

    public static function store(Request $request): void
    {
        $user = AuthMiddleware::requireAdmin();

        $file = $request->file('file');
        if ($file === null) {
            Response::error('Validation failed.', 422, ['file' => ['A file is required.']]);
            return;
        }

        try {
            $stored = Upload::store($file);
        } catch (RuntimeException $e) {
            Response::error($e->getMessage(), 422);
            return;
        }

        $id = MediaRepository::create([
            'filename' => $stored['filename'],
            'file_path' => $stored['path'],
            'alt_text' => self::nullableString($request->input('alt_text')),
            'uploaded_by' => $user->id,
        ]);

        Response::success(Media::fromRow(MediaRepository::findById($id))->toArray(), 201);
    }

    public static function update(Request $request, array $args): void
    {
        AuthMiddleware::requireAdmin();
        $id = (int) $args['id'];

        if (MediaRepository::findById($id) === null) {
            Response::notFound('Media item not found.');
            return;
        }

        MediaRepository::updateAltText($id, self::nullableString($request->input('alt_text')));
        Response::success(Media::fromRow(MediaRepository::findById($id))->toArray());
    }

    public static function destroy(Request $request, array $args): void
    {
        AuthMiddleware::requireAdmin();
        $id = (int) $args['id'];
        $existing = MediaRepository::findById($id);

        if ($existing === null) {
            Response::notFound('Media item not found.');
            return;
        }

        // Same reasoning as CategoryRepository::countPostsUsing() — don't
        // silently break a post's cover image out from under it.
        $usageCount = MediaRepository::countPostsUsingCover($existing['file_path']);
        if ($usageCount > 0) {
            Response::error(
                "Cannot delete: {$usageCount} post(s) use this image as their cover.",
                409
            );
            return;
        }

        MediaRepository::delete($id);
        Upload::delete($existing['file_path']);
        Response::success(['message' => 'Media item deleted.']);
    }

    private static function nullableString(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;
        return $value === null || $value === '' ? null : (string) $value;
    }
}
