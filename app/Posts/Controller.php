<?php

/**
 * Public:  GET /posts, GET /posts/{id}, POST /posts/{id}/view
 * Author:  GET/POST /author/posts, PATCH/DELETE /author/posts/{id}  (own posts only)
 * Admin:   GET/POST /admin/posts, PATCH/DELETE /admin/posts/{id}    (any post, any author)
 */
class PostController
{
    private const STATUSES = ['draft', 'published'];
    private const DEFAULT_PER_PAGE = 10;
    private const MAX_PER_PAGE = 50;

    // ---------------------------------------------------------------
    // Public
    // ---------------------------------------------------------------

    public static function index(Request $request): void
    {
        [$page, $perPage] = self::pagination($request);
        $category = self::nullableString($request->input('category'));
        $search = self::nullableString($request->input('search'));

        $result = PostRepository::publishedPaginated($page, $perPage, $category, $search);

        Response::success(
            array_map(fn (array $row) => Post::fromRow($row)->toArray(), $result['rows']),
            200,
            self::meta($page, $perPage, $result['total'])
        );
    }

    public static function show(Request $request, array $args): void
    {
        $row = PostRepository::findPublishedById((int) $args['id']);

        if ($row === null) {
            Response::notFound('Post not found.');
            return;
        }

        Response::success(Post::fromRow($row)->toArray());
    }

    /** No auth required — anonymous readers trigger this once per page view. */
    public static function recordView(Request $request, array $args): void
    {
        $id = (int) $args['id'];
        $row = PostRepository::findPublishedById($id);

        if ($row === null) {
            Response::notFound('Post not found.');
            return;
        }

        PostRepository::incrementViews($id);
        Response::success(['message' => 'View recorded.']);
    }

    // ---------------------------------------------------------------
    // Author (own posts only)
    // ---------------------------------------------------------------

    public static function authorIndex(Request $request): void
    {
        $user = AuthMiddleware::requireUser();
        [$page, $perPage] = self::pagination($request);

        $result = PostRepository::byAuthorPaginated($user->id, $page, $perPage);

        Response::success(
            array_map(fn (array $row) => Post::fromRow($row)->toArray(), $result['rows']),
            200,
            self::meta($page, $perPage, $result['total'])
        );
    }

    public static function authorStore(Request $request): void
    {
        $user = AuthMiddleware::requireUser();

        $errors = self::validate($request->body);
        if ($errors !== []) {
            Response::error('Validation failed.', 422, $errors);
            return;
        }

        $id = self::persistNew($request, $user->id);
        if ($id === null) {
            Response::error('The selected category does not exist.', 422, ['category_id' => ['Invalid category.']]);
            return;
        }

        Response::success(Post::fromRow(PostRepository::findById($id))->toArray(), 201);
    }

    public static function authorUpdate(Request $request, array $args): void
    {
        $user = AuthMiddleware::requireUser();
        $id = (int) $args['id'];
        $existing = PostRepository::findById($id);

        if ($existing === null) {
            Response::notFound('Post not found.');
            return;
        }

        if ((int) $existing['author_id'] !== $user->id) {
            Response::forbidden('You can only edit your own posts.');
            return;
        }

        self::applyUpdate($request, $existing, $id);
    }

    public static function authorDestroy(Request $request, array $args): void
    {
        $user = AuthMiddleware::requireUser();
        $id = (int) $args['id'];
        $existing = PostRepository::findById($id);

        if ($existing === null) {
            Response::notFound('Post not found.');
            return;
        }

        if ((int) $existing['author_id'] !== $user->id) {
            Response::forbidden('You can only delete your own posts.');
            return;
        }

        PostRepository::softDelete($id);
        Response::success(['message' => 'Post deleted.']);
    }

    // ---------------------------------------------------------------
    // Admin (any post, any author)
    // ---------------------------------------------------------------

    public static function adminIndex(Request $request): void
    {
        AuthMiddleware::requireAdmin();
        [$page, $perPage] = self::pagination($request);
        $status = self::nullableString($request->input('status'));

        $result = PostRepository::allPaginated($page, $perPage, $status);

        Response::success(
            array_map(fn (array $row) => Post::fromRow($row)->toArray(), $result['rows']),
            200,
            self::meta($page, $perPage, $result['total'])
        );
    }

    public static function adminShow(Request $request, array $args): void
    {
        AuthMiddleware::requireAdmin();
        $row = PostRepository::findById((int) $args['id']);

        if ($row === null) {
            Response::notFound('Post not found.');
            return;
        }

        Response::success(Post::fromRow($row)->toArray());
    }

    public static function adminStore(Request $request): void
    {
        AuthMiddleware::requireAdmin();

        $errors = self::validate($request->body);
        $authorId = (int) $request->input('author_id', 0);

        if ($authorId <= 0) {
            $errors['author_id'][] = 'The author_id field is required.';
        } elseif (AuthRepository::findById($authorId) === null) {
            $errors['author_id'][] = 'The selected author does not exist.';
        }

        if ($errors !== []) {
            Response::error('Validation failed.', 422, $errors);
            return;
        }

        $id = self::persistNew($request, $authorId);
        if ($id === null) {
            Response::error('The selected category does not exist.', 422, ['category_id' => ['Invalid category.']]);
            return;
        }

        Response::success(Post::fromRow(PostRepository::findById($id))->toArray(), 201);
    }

    public static function adminUpdate(Request $request, array $args): void
    {
        AuthMiddleware::requireAdmin();
        $id = (int) $args['id'];
        $existing = PostRepository::findById($id);

        if ($existing === null) {
            Response::notFound('Post not found.');
            return;
        }

        $authorId = $request->input('author_id');
        if ($authorId !== null) {
            if (AuthRepository::findById((int) $authorId) === null) {
                Response::error('Validation failed.', 422, ['author_id' => ['The selected author does not exist.']]);
                return;
            }
            PostRepository::updateAuthor($id, (int) $authorId);
        }

        self::applyUpdate($request, $existing, $id);
    }

    public static function adminDestroy(Request $request, array $args): void
    {
        AuthMiddleware::requireAdmin();
        $id = (int) $args['id'];

        if (PostRepository::findById($id) === null) {
            Response::notFound('Post not found.');
            return;
        }

        PostRepository::softDelete($id);
        Response::success(['message' => 'Post deleted.']);
    }

    // ---------------------------------------------------------------
    // Shared helpers
    // ---------------------------------------------------------------

    private static function persistNew(Request $request, int $authorId): ?int
    {
        $categoryId = self::nullableInt($request->input('category_id'));
        if ($categoryId !== null && CategoryRepository::findById($categoryId) === null) {
            return null;
        }

        $title = trim((string) $request->input('title'));
        $status = (string) $request->input('status');

        return PostRepository::create([
            'title' => $title,
            'slug' => self::uniqueSlug($title),
            'excerpt' => self::nullableString($request->input('excerpt')),
            'body' => (string) $request->input('body'),
            'cover_image' => self::nullableString($request->input('cover_image')),
            'status' => $status,
            'author_id' => $authorId,
            'category_id' => $categoryId,
            'published_date' => $status === 'published' ? ((string) $request->input('published_date') ?: date('Y-m-d H:i:s')) : null,
            'seo_title' => self::nullableString($request->input('seo_title')),
            'seo_description' => self::nullableString($request->input('seo_description')),
        ]);
    }

    /** Shared PATCH body for both author and admin update endpoints (title/category re-validated the same way either caller invokes it). */
    private static function applyUpdate(Request $request, array $existing, int $id): void
    {
        $errors = self::validate($request->body);
        if ($errors !== []) {
            Response::error('Validation failed.', 422, $errors);
            return;
        }

        $categoryId = self::nullableInt($request->input('category_id'));
        if ($categoryId !== null && CategoryRepository::findById($categoryId) === null) {
            Response::error('Validation failed.', 422, ['category_id' => ['Invalid category.']]);
            return;
        }

        $title = trim((string) $request->input('title'));
        $status = (string) $request->input('status');
        $slug = $title === $existing['title'] ? $existing['slug'] : self::uniqueSlug($title, $id);

        // Only stamp published_date the first time a post goes live — flipping
        // published -> draft -> published again shouldn't reset it, and a
        // still-draft post has none.
        $publishedDate = $existing['published_date'];
        if ($status === 'published' && $publishedDate === null) {
            $publishedDate = date('Y-m-d H:i:s');
        } elseif ($status === 'draft') {
            $publishedDate = $existing['published_date']; // keep history if it was published before
        }

        PostRepository::update($id, [
            'title' => $title,
            'slug' => $slug,
            'excerpt' => self::nullableString($request->input('excerpt')),
            'body' => (string) $request->input('body'),
            'cover_image' => self::nullableString($request->input('cover_image')),
            'status' => $status,
            'category_id' => $categoryId,
            'published_date' => $publishedDate,
            'seo_title' => self::nullableString($request->input('seo_title')),
            'seo_description' => self::nullableString($request->input('seo_description')),
        ]);

        Response::success(Post::fromRow(PostRepository::findById($id))->toArray());
    }

    private static function validate(array $body): array
    {
        $validator = new Validator($body, [
            'title' => 'required|max:255',
            'body' => 'required',
            'status' => 'required|in:' . implode(',', self::STATUSES),
            'excerpt' => 'max:500',
            'seo_title' => 'max:255',
            'seo_description' => 'max:500',
        ]);

        return $validator->fails() ? $validator->errors() : [];
    }

    private static function uniqueSlug(string $title, ?int $exceptId = null): string
    {
        $base = Str::slugify($title);
        $slug = $base;
        $suffix = 2;

        while (PostRepository::slugExists($slug, $exceptId)) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    private static function pagination(Request $request): array
    {
        $page = max(1, (int) $request->input('page', 1));
        $perPage = (int) $request->input('per_page', self::DEFAULT_PER_PAGE);
        $perPage = $perPage > 0 ? min($perPage, self::MAX_PER_PAGE) : self::DEFAULT_PER_PAGE;

        return [$page, $perPage];
    }

    private static function meta(int $page, int $perPage, int $total): array
    {
        return [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }

    private static function nullableString(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;
        return $value === null || $value === '' ? null : (string) $value;
    }

    private static function nullableInt(mixed $value): ?int
    {
        return $value === null || $value === '' ? null : (int) $value;
    }
}
