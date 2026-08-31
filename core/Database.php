<?php

/**
 * PDO connection singleton, isolated to this application's own database
 * (blog_* tables only — never used to touch ads.skoolyst.com or
 * teachers.skoolyst.com tables).
 */
class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection === null) {
            $host = Config::get('database.host', '127.0.0.1');
            $port = Config::get('database.port', '3306');
            $name = Config::get('database.name');
            $charset = Config::get('database.charset', 'utf8mb4');
            $user = Config::get('database.user');
            $pass = Config::get('database.password');

            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";

            try {
                self::$connection = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                // Never leak DSN/credentials. Let the caller decide how to surface
                // this — index.php's exception handler turns it into a JSON 500,
                // bin/*.php CLI scripts print it to STDERR. Don't couple this
                // class to the HTTP-only Response class.
                error_log('[blog.skoolyst.com] DB connection failed: ' . $e->getMessage());
                throw new RuntimeException('Database connection failed.', 0, $e);
            }
        }

        return self::$connection;
    }

    /** Convenience: run a prepared SELECT and return all rows. */
    public static function select(string $sql, array $params = []): array
    {
        $stmt = self::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Convenience: run a prepared SELECT and return the first row (or null). */
    public static function selectOne(string $sql, array $params = []): ?array
    {
        $stmt = self::connection()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /** Convenience: run an INSERT/UPDATE/DELETE, return affected row count. */
    public static function execute(string $sql, array $params = []): int
    {
        $stmt = self::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public static function lastInsertId(): string
    {
        return self::connection()->lastInsertId();
    }
}
