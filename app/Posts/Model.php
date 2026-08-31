<?php

/**
 * Lightweight DTO for a post row, optionally enriched with joined
 * category/author display fields (present when the repository query
 * joins them; null otherwise so this class works for both cases).
 */
class Post
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $slug,
        public readonly ?string $excerpt,
        public readonly string $body,
        public readonly ?string $coverImage,
        public readonly string $status,
        public readonly int $authorId,
        public readonly ?int $categoryId,
        public readonly ?string $publishedDate,
        public readonly int $views,
        public readonly ?string $seoTitle,
        public readonly ?string $seoDescription,
        public readonly ?string $authorName = null,
        public readonly ?string $categoryName = null,
        public readonly ?string $categorySlug = null,
        public readonly ?string $categoryColor = null,
    ) {
    }

    public static function fromRow(array $row): self
    {
        return new self(
            (int) $row['id'],
            (string) $row['title'],
            (string) $row['slug'],
            $row['excerpt'] !== null ? (string) $row['excerpt'] : null,
            (string) $row['body'],
            $row['cover_image'] !== null ? (string) $row['cover_image'] : null,
            (string) $row['status'],
            (int) $row['author_id'],
            $row['category_id'] !== null ? (int) $row['category_id'] : null,
            $row['published_date'] !== null ? (string) $row['published_date'] : null,
            (int) $row['views'],
            $row['seo_title'] !== null ? (string) $row['seo_title'] : null,
            $row['seo_description'] !== null ? (string) $row['seo_description'] : null,
            $row['author_name'] ?? null,
            $row['category_name'] ?? null,
            $row['category_slug'] ?? null,
            $row['category_color'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'body' => $this->body,
            'cover_image' => $this->coverImage,
            'status' => $this->status,
            'author_id' => $this->authorId,
            'category_id' => $this->categoryId,
            'published_date' => $this->publishedDate,
            'views' => $this->views,
            'seo_title' => $this->seoTitle,
            'seo_description' => $this->seoDescription,
            'author_name' => $this->authorName,
            'category' => $this->categoryId === null ? null : [
                'id' => $this->categoryId,
                'name' => $this->categoryName,
                'slug' => $this->categorySlug,
                'color' => $this->categoryColor,
            ],
        ];
    }
}
