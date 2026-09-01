<?php

/**
 * Lightweight DTO for a public author listing row.
 * Deliberately narrow: only what a public "meet the team" page needs
 * (id, name, avatar, bio). Never exposes email, password_hash, role,
 * or status — those stay behind Auth/Admin-only reads of blog_users.
 */
class Author
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $avatarUrl,
        public readonly ?string $bio,
    ) {
    }

    public static function fromRow(array $row): self
    {
        return new self(
            (int) $row['id'],
            (string) $row['name'],
            $row['avatar_url'] !== null ? (string) $row['avatar_url'] : null,
            $row['bio'] !== null ? (string) $row['bio'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'avatar_url' => $this->avatarUrl,
            'bio' => $this->bio,
        ];
    }
}
