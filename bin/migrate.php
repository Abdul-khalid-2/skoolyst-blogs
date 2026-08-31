#!/usr/bin/env php
<?php

/**
 * Run pending database migrations.
 * Usage: php bin/migrate.php
 */

declare(strict_types=1);

require __DIR__ . '/../core/Env.php';
require __DIR__ . '/../core/Config.php';
require __DIR__ . '/../core/Database.php';
require __DIR__ . '/../core/Migrator.php';

Env::load(__DIR__ . '/../.env');
Config::init(__DIR__ . '/../config');

fwrite(STDOUT, "Connecting to database...\n");

try {
    $migrator = new Migrator(__DIR__ . '/../database/migrations');
    $applied = $migrator->run();
} catch (Throwable $e) {
    fwrite(STDERR, "Migration failed: " . $e->getMessage() . "\n");
    exit(1);
}

if ($applied === []) {
    fwrite(STDOUT, "Nothing to migrate — database is already up to date.\n");
    exit(0);
}

fwrite(STDOUT, "Applied " . count($applied) . " migration(s):\n");
foreach ($applied as $name) {
    fwrite(STDOUT, "  - {$name}\n");
}
