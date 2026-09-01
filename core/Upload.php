<?php

/**
 * Shared file-upload handler. Section 6 flagged this as a pending core
 * piece ("upload handler") — built here, where it's actually needed, rather
 * than speculatively earlier. Only the Media module calls this for now.
 */
class Upload
{
    private const EXTENSION_BY_MIME = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    /**
     * Validates and moves an uploaded file (a $_FILES-shaped array, e.g.
     * from Request::file()) into the configured upload directory.
     * Returns ['filename' => ..., 'path' => ...] (path is web-relative,
     * suitable for storing in blog_media.file_path / blog_posts.cover_image).
     *
     * @throws RuntimeException on any validation or filesystem failure —
     *         callers turn this into a 422 Response, keeping this class
     *         HTTP-agnostic (same reasoning as Database's own exceptions).
     */
    public static function store(array $file): array
    {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('The file failed to upload.');
        }

        $maxSize = (int) Config::get('app.upload_max_size', 5 * 1024 * 1024);
        if ($file['size'] > $maxSize) {
            $mb = round($maxSize / 1024 / 1024, 1);
            throw new RuntimeException("The file must not be larger than {$mb}MB.");
        }

        // Trust the file's actual bytes, not the client-supplied MIME type.
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowed = Config::get('app.upload_allowed_mimes', []);
        if (!in_array($mime, $allowed, true)) {
            throw new RuntimeException('Only JPEG, PNG, GIF, or WebP images are allowed.');
        }

        $uploadDir = rtrim((string) Config::get('app.upload_path'), '/');
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
            throw new RuntimeException('Could not prepare the upload directory.');
        }

        $extension = self::EXTENSION_BY_MIME[$mime] ?? 'bin';
        $filename = bin2hex(random_bytes(12)) . '.' . $extension;
        $destination = $uploadDir . '/' . $filename;

        $moved = is_uploaded_file($file['tmp_name'])
            ? move_uploaded_file($file['tmp_name'], $destination)
            : rename($file['tmp_name'], $destination); // only hit by test harnesses that fake $_FILES without a real HTTP upload

        if (!$moved) {
            throw new RuntimeException('Could not save the uploaded file.');
        }

        return [
            'filename' => $filename,
            // Web-relative path. Docroot is the repo root (see .htaccess), and
            // the physical file lives under public/uploads/media/ (that folder
            // — with its .gitkeep — predates this module, from Section 6's
            // scaffold), so the URL must include the /public prefix or the
            // file 404s despite existing on disk.
            'path' => '/public/uploads/media/' . $filename,
        ];
    }

    /** Best-effort delete of a previously stored file; silently no-ops if it's already gone. */
    public static function delete(string $webPath): void
    {
        // $webPath already includes the /public prefix store() returned —
        // don't add it again here.
        $full = dirname(__DIR__) . $webPath;

        if (is_file($full)) {
            @unlink($full);
        }
    }
}
