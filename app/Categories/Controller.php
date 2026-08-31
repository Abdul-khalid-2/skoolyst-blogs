<?php

/**
 * GET /categories, GET /categories/{slug} are public.
 * POST/PATCH/DELETE /categories require an admin session (AuthMiddleware).
 */
class CategoryController
{
    private const HEX_COLOR_PATTERN = '/^#[0-9a-fA-F]{6}$/';
    private const DEFAULT_COLOR = '#4361ee';

    public static function index(Request $request): void
    {
        $rows = CategoryRepository::all();

        $data = array_map(function (array $row) {
            $arr = Category::fromRow($row)->toArray();
            $arr['post_count'] = (int) $row['post_count'];
            return $arr;
        }, $rows);

        Response::success($data);
    }

    public static function show(Request $request, array $args): void
    {
        $row = CategoryRepository::findBySlug($args['slug']);

        if ($row === null) {
            Response::notFound('Category not found.');
            return;
        }

        Response::success(Category::fromRow($row)->toArray());
    }

    public static function store(Request $request): void
    {
        AuthMiddleware::requireAdmin();

        $errors = self::validate($request->body);
        if ($errors !== []) {
            Response::error('Validation failed.', 422, $errors);
            return;
        }

        $name = trim((string) $request->input('name'));
        $slug = self::uniqueSlug($name);

        $id = CategoryRepository::create([
            'name' => $name,
            'slug' => $slug,
            'description' => self::nullableString($request->input('description')),
            'color' => $request->input('color') ?: self::DEFAULT_COLOR,
        ]);

        $row = CategoryRepository::findById($id);
        Response::success(Category::fromRow($row)->toArray(), 201);
    }

    public static function update(Request $request, array $args): void
    {
        AuthMiddleware::requireAdmin();

        $id = (int) $args['id'];
        $existing = CategoryRepository::findById($id);

        if ($existing === null) {
            Response::notFound('Category not found.');
            return;
        }

        $errors = self::validate($request->body);
        if ($errors !== []) {
            Response::error('Validation failed.', 422, $errors);
            return;
        }

        $name = trim((string) $request->input('name'));

        // Only re-slug if the name actually changed — keeps existing links/SEO
        // stable when someone just tweaks the description or color.
        $slug = $name === $existing['name']
            ? $existing['slug']
            : self::uniqueSlug($name, $id);

        CategoryRepository::update($id, [
            'name' => $name,
            'slug' => $slug,
            'description' => self::nullableString($request->input('description')),
            'color' => $request->input('color') ?: self::DEFAULT_COLOR,
        ]);

        $row = CategoryRepository::findById($id);
        Response::success(Category::fromRow($row)->toArray());
    }

    public static function destroy(Request $request, array $args): void
    {
        AuthMiddleware::requireAdmin();

        $id = (int) $args['id'];
        $existing = CategoryRepository::findById($id);

        if ($existing === null) {
            Response::notFound('Category not found.');
            return;
        }

        // Mirrors the dashboard.js mock guard: don't allow deleting a
        // category that's still attached to posts.
        $postCount = CategoryRepository::countPostsUsing($id);
        if ($postCount > 0) {
            Response::error(
                "Cannot delete: {$postCount} post(s) use this category.",
                409
            );
            return;
        }

        CategoryRepository::delete($id);
        Response::success(['message' => 'Category deleted.']);
    }

    private static function validate(array $body): array
    {
        $validator = new Validator($body, [
            'name' => 'required|max:120',
            'description' => 'max:500',
        ]);

        $errors = $validator->fails() ? $validator->errors() : [];

        $color = $body['color'] ?? null;
        if ($color !== null && $color !== '' && !preg_match(self::HEX_COLOR_PATTERN, (string) $color)) {
            $errors['color'][] = 'The color field must be a valid hex color (e.g. #4361ee).';
        }

        return $errors;
    }

    private static function nullableString(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;
        return $value === null || $value === '' ? null : (string) $value;
    }

    /** Slugify the name and de-duplicate against existing slugs (name-2, name-3, ...). */
    private static function uniqueSlug(string $name, ?int $exceptId = null): string
    {
        $base = Str::slugify($name);
        $slug = $base;
        $suffix = 2;

        while (CategoryRepository::slugExists($slug, $exceptId)) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }
}
