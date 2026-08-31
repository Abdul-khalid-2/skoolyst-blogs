<?php

/**
 * Lightweight DTO for a comment row. Never exposes author_email publicly —
 * toArray() (used by the public post-show embed) omits it; toAdminArray()
 * (moderation queue) includes it since admins need to identify/contact
 * commenters and spot spam patterns.
 */
class Comment
{
    public function __construct(
        public readonly int $id,
        public readonly int $postId,
        public readonly string $authorName,
        public readonly string $authorEmail,
        public readonly string $body,
        public readonly string $status,
        public readonly string $createdAt,
        public readonly ?string $postTitle = null,
    ) {
    }

    public static function fromRow(array $row): self
    {
        return new self(
            (int) $row['id'],
            (int) $row['post_id'],
            (string) $row['author_name'],
            (string) $row['author_email'],
            (string) $row['body'],
            (string) $row['status'],
            (string) $row['created_at'],
            $row['post_title'] ?? null,
        );
    }

    /** Public shape — embedded under a post's `comments` array. Never leaks the commenter's email. */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'author_name' => $this->authorName,
            'body' => $this->body,
            'created_at' => $this->createdAt,
        ];
    }

    /** Admin moderation-queue shape — includes email + status + which post it's on. */
    public function toAdminArray(): array
    {
        return [
            'id' => $this->id,
            'post_id' => $this->postId,
            'post_title' => $this->postTitle,
            'author_name' => $this->authorName,
            'author_email' => $this->authorEmail,
            'body' => $this->body,
            'status' => $this->status,
            'created_at' => $this->createdAt,
        ];
    }
}
