<?php

/**
 * Public:  POST /posts/{id}/comments  (always saved as 'pending')
 * Admin:   GET /admin/comments, PATCH /admin/comments/{id}, DELETE /admin/comments/{id}
 *
 * Approved comments themselves are returned embedded in PostController::show()'s
 * response (Section 8 doesn't list a separate GET comments endpoint) rather
 * than a route of their own here.
 */
class CommentController
{
    private const VALID_STATUSES = ['pending', 'approved', 'spam'];
    private const DUPLICATE_WINDOW_SECONDS = 30;
    private const DEFAULT_PER_PAGE = 20;
    private const MAX_PER_PAGE = 100;

    public static function store(Request $request, array $args): void
    {
        $postId = (int) $args['id'];

        if (PostRepository::findPublishedById($postId) === null) {
            Response::notFound('Post not found.');
            return;
        }

        $validator = new Validator($request->body, [
            'author_name' => 'required|max:150',
            'author_email' => 'required|email|max:190',
            'body' => 'required|max:2000',
        ]);

        if ($validator->fails()) {
            Response::error('Validation failed.', 422, $validator->errors());
            return;
        }

        $email = trim((string) $request->input('author_email'));

        // Lightweight guard against double-click/bot resubmission on the same
        // post. Not a real rate limiter (Section 6's is still pending) — just
        // enough to stop the obvious case until that lands.
        if (CommentRepository::recentDuplicate($postId, $email, self::DUPLICATE_WINDOW_SECONDS)) {
            Response::error('You\'ve already commented recently — please wait a moment before trying again.', 429);
            return;
        }

        $id = CommentRepository::create([
            'post_id' => $postId,
            'author_name' => trim((string) $request->input('author_name')),
            'author_email' => $email,
            'body' => trim((string) $request->input('body')),
            'status' => 'pending',
        ]);

        Response::success(
            ['id' => $id, 'message' => 'Comment submitted and awaiting moderation.'],
            201
        );
    }

    public static function adminIndex(Request $request): void
    {
        AuthMiddleware::requireAdmin();

        $page = max(1, (int) $request->input('page', 1));
        $perPage = (int) $request->input('per_page', self::DEFAULT_PER_PAGE);
        $perPage = $perPage > 0 ? min($perPage, self::MAX_PER_PAGE) : self::DEFAULT_PER_PAGE;
        $status = $request->input('status');

        if ($status !== null && !in_array($status, self::VALID_STATUSES, true)) {
            Response::error('Validation failed.', 422, ['status' => ['Must be one of: ' . implode(', ', self::VALID_STATUSES) . '.']]);
            return;
        }

        $result = CommentRepository::paginated($page, $perPage, $status);

        Response::success(
            array_map(fn (array $row) => Comment::fromRow($row)->toAdminArray(), $result['rows']),
            200,
            [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $result['total'],
                'total_pages' => (int) ceil($result['total'] / $perPage),
            ]
        );
    }

    public static function updateStatus(Request $request, array $args): void
    {
        AuthMiddleware::requireAdmin();

        $id = (int) $args['id'];
        $existing = CommentRepository::findById($id);

        if ($existing === null) {
            Response::notFound('Comment not found.');
            return;
        }

        $status = (string) $request->input('status');
        if (!in_array($status, self::VALID_STATUSES, true)) {
            Response::error('Validation failed.', 422, ['status' => ['Must be one of: ' . implode(', ', self::VALID_STATUSES) . '.']]);
            return;
        }

        CommentRepository::updateStatus($id, $status);
        Response::success(Comment::fromRow(CommentRepository::findById($id))->toAdminArray());
    }

    public static function destroy(Request $request, array $args): void
    {
        AuthMiddleware::requireAdmin();

        $id = (int) $args['id'];
        if (CommentRepository::findById($id) === null) {
            Response::notFound('Comment not found.');
            return;
        }

        CommentRepository::delete($id);
        Response::success(['message' => 'Comment deleted.']);
    }
}
