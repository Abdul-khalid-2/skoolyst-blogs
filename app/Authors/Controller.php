<?php

/**
 * Public: GET /authors, GET /authors/{id}
 * Read-only — author accounts themselves are created/managed via the
 * Auth module's seed data; this module only exposes the public-facing
 * subset of blog_users (name, avatar, bio) for pages like about.html.
 */
class AuthorController
{
    public static function index(Request $request): void
    {
        $rows = AuthorRepository::allActive();

        Response::success(array_map(
            fn (array $row) => Author::fromRow($row)->toArray(),
            $rows
        ));
    }

    public static function show(Request $request, array $args): void
    {
        $row = AuthorRepository::findActiveById((int) $args['id']);

        if ($row === null) {
            Response::notFound('Author not found.');
            return;
        }

        Response::success(Author::fromRow($row)->toArray());
    }
}
