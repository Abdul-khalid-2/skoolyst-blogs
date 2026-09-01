<?php

/**
 * Wraps the incoming HTTP request: method, path (with /api/v1 stripped),
 * query params, parsed JSON body, and headers.
 */
class Request
{
    // Not marked `readonly` on purpose (only widened via withPath() below) —
    // keeps this compatible with PHP 8.1, since readonly-in-clone needs 8.3+.
    public string $method;
    public string $path;
    public array $query;
    public array $body;
    public array $headers;
    public array $files;

    public function __construct()
    {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $this->path = '/' . trim($uri, '/');

        $this->query = $_GET;
        $this->body = $this->parseBody();
        $this->headers = $this->parseHeaders();
        $this->files = $_FILES;
    }

    private function parseBody(): array
    {
        if (!in_array($this->method, ['POST', 'PUT', 'PATCH'], true)) {
            return [];
        }

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input') ?: '';
            $decoded = json_decode($raw, true);
            return is_array($decoded) ? $decoded : [];
        }

        // multipart/form-data or x-www-form-urlencoded
        return $_POST;
    }

    private function parseHeaders(): array
    {
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            return is_array($headers) ? $headers : [];
        }

        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$name] = $value;
            }
        }
        return $headers;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    /** Returns the uploaded file's $_FILES entry for $key, or null if none was sent. */
    public function file(string $key): ?array
    {
        return isset($this->files[$key]) && $this->files[$key]['error'] !== UPLOAD_ERR_NO_FILE
            ? $this->files[$key]
            : null;
    }

    public function bearerToken(): ?string
    {
        $auth = $this->headers['Authorization'] ?? $this->headers['authorization'] ?? '';
        if (str_starts_with($auth, 'Bearer ')) {
            return substr($auth, 7);
        }
        return null;
    }

    /** Returns a copy of this request with a different path (used to strip the /api/v1 prefix before routing). */
    public function withPath(string $newPath): self
    {
        $clone = clone $this;
        $clone->path = '/' . trim($newPath, '/');
        return $clone;
    }
}
