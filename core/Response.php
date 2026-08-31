<?php

/**
 * Sends uniform JSON responses. Every API response goes through here so
 * error shape stays consistent across the whole app.
 */
class Response
{
    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public static function success(mixed $data = null, int $status = 200, array $meta = []): void
    {
        $payload = ['success' => true];

        if ($data !== null) {
            $payload['data'] = $data;
        }

        if ($meta !== []) {
            $payload['meta'] = $meta;
        }

        self::json($payload, $status);
    }

    public static function error(string $message, int $status = 400, array $errors = []): void
    {
        $payload = [
            'success' => false,
            'error' => $message,
        ];

        if ($errors !== []) {
            $payload['errors'] = $errors;
        }

        self::json($payload, $status);
    }

    public static function notFound(string $message = 'Not found.'): void
    {
        self::error($message, 404);
    }

    public static function methodNotAllowed(string $message = 'Method not allowed.'): void
    {
        self::error($message, 405);
    }

    public static function unauthorized(string $message = 'Unauthorized.'): void
    {
        self::error($message, 401);
    }

    public static function forbidden(string $message = 'Forbidden.'): void
    {
        self::error($message, 403);
    }
}
