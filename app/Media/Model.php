<?php

/**
 * Lightweight DTO for a media library row.
 */
class Media
{
    public function __construct(
        public readonly int $id,
        public readonly string $filename,
        public readonly string $filePath,
        public readonly ?string $altText,
        public readonly ?int $uploadedBy,
        public readonly string $createdAt,
        public readonly ?string $uploadedByName = null,
    ) {
    }

    public static function fromRow(array $row): self
    {
        return new self(
            (int) $row['id'],
            (string) $row['filename'],
            (string) $row['file_path'],
            $row['alt_text'] !== null ? (string) $row['alt_text'] : null,
            $row['uploaded_by'] !== null ? (int) $row['uploaded_by'] : null,
            (string) $row['created_at'],
            $row['uploaded_by_name'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'filename' => $this->filename,
            'file_path' => $this->filePath,
            'alt_text' => $this->altText,
            'uploaded_by' => $this->uploadedBy,
            'uploaded_by_name' => $this->uploadedByName,
            'created_at' => $this->createdAt,
        ];
    }
}
