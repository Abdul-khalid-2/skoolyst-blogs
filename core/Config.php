<?php

/**
 * Loads and caches config/*.php files, exposing dot-notation access:
 *   Config::get('app.url')
 *   Config::get('database.host')
 */
class Config
{
    private static array $items = [];
    private static ?string $configPath = null;

    public static function init(string $configPath): void
    {
        self::$configPath = rtrim($configPath, '/');
        self::$items = [];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        [$file, $rest] = array_pad(explode('.', $key, 2), 2, null);

        if (!isset(self::$items[$file])) {
            self::$items[$file] = self::loadFile($file);
        }

        if ($rest === null) {
            return self::$items[$file] ?? $default;
        }

        $segments = explode('.', $rest);
        $value = self::$items[$file];

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    private static function loadFile(string $file): array
    {
        $path = self::$configPath . '/' . $file . '.php';

        if (!is_file($path)) {
            return [];
        }

        $data = require $path;

        return is_array($data) ? $data : [];
    }
}
