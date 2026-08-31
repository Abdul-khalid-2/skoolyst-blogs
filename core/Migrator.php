<?php

/**
 * Runs .sql files from database/migrations/ in filename order, tracking
 * which ones have already run in blog_migrations so re-running is safe.
 */
class Migrator
{
    private PDO $pdo;
    private string $path;

    public function __construct(string $migrationsPath)
    {
        $this->pdo = Database::connection();
        $this->path = rtrim($migrationsPath, '/');
    }

    /** @return string[] names of migrations that were actually applied this run */
    public function run(): array
    {
        $this->ensureMigrationsTableExists();

        $applied = $this->appliedMigrations();
        $files = $this->migrationFiles();
        $newlyApplied = [];

        foreach ($files as $file) {
            $name = basename($file);

            if (in_array($name, $applied, true)) {
                continue;
            }

            $sql = file_get_contents($file);
            if ($sql === false || trim($sql) === '') {
                continue;
            }

            // Note: DDL statements (CREATE TABLE etc.) trigger an implicit
            // commit in MySQL/MariaDB, so wrapping them in an explicit
            // transaction wouldn't actually be atomic — we just run them
            // directly and record success only if every statement succeeds.
            try {
                foreach ($this->splitStatements($sql) as $statement) {
                    $this->pdo->exec($statement);
                }

                $stmt = $this->pdo->prepare('INSERT INTO blog_migrations (migration) VALUES (:migration)');
                $stmt->execute(['migration' => $name]);

                $newlyApplied[] = $name;
            } catch (Throwable $e) {
                throw new RuntimeException("Migration failed: {$name} — " . $e->getMessage(), 0, $e);
            }
        }

        return $newlyApplied;
    }

    private function ensureMigrationsTableExists(): void
    {
        $firstMigration = $this->path . '/0001_create_blog_migrations_table.sql';
        if (is_file($firstMigration)) {
            $this->pdo->exec(file_get_contents($firstMigration));
        }
    }

    /** @return string[] */
    private function appliedMigrations(): array
    {
        $stmt = $this->pdo->query('SELECT migration FROM blog_migrations');
        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    /** @return string[] absolute file paths, sorted */
    private function migrationFiles(): array
    {
        $files = glob($this->path . '/*.sql') ?: [];
        sort($files, SORT_STRING);
        return $files;
    }

    /** Splits a .sql file on statement-terminating semicolons (naive but fine for our migration files — no stored procedures). */
    private function splitStatements(string $sql): array
    {
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        return array_values($statements);
    }
}
