<?php

/**
 * PHPUnit bootstrap: recreate the test database before any test runs.
 *
 * WHY: Laravel's db:wipe generates a single DROP TABLE t1, t2, ... tN without
 * IF EXISTS. On MariaDB 11.8, dropping 180+ tables in one statement and then
 * immediately re-creating them exposes an InnoDB data dictionary bug where
 * subsequent ALTER TABLE statements fail with "Table doesn't exist" — even
 * seconds after CREATE TABLE succeeded. This leaves the DB in a partial state
 * that cascades: the next db:wipe call includes tables that may not exist yet,
 * which causes the same failure again in a self-reinforcing loop.
 *
 * Dropping and re-creating the database guarantees the InnoDB data dictionary
 * is fully reset, so the first migrate:fresh call (from RefreshDatabase) always
 * finds repositoryExists()=false, skips db:wipe, and runs migrate on an empty
 * schema — which works reliably every time.
 *
 * SAFETY: Protected by the same allowedDatabases check as TestCase::setUp().
 */

require __DIR__ . '/../vendor/autoload.php';

(function () {
    $app = require __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    $connection    = config('database.default');
    $database      = config("database.connections.{$connection}.database");
    $allowedDbs    = ['agro365_test'];

    if (! in_array($database, $allowedDbs, true)) {
        throw new RuntimeException(
            "🚨 PELIGRO: bootstrap refuses to recreate '{$database}'. " .
            "Only agro365_test is allowed. Check your .env.testing."
        );
    }

    $host     = config("database.connections.{$connection}.host");
    $port     = config("database.connections.{$connection}.port", 3306);
    $username = config("database.connections.{$connection}.username");
    $password = config("database.connections.{$connection}.password");

    $pdo = new PDO("mysql:host={$host};port={$port}", $username, $password);
    $pdo->exec("DROP DATABASE IF EXISTS `{$database}`");
    $pdo->exec(
        "CREATE DATABASE `{$database}` " .
        "CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
    );
})();
