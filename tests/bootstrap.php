<?php

/**
 * PHPUnit bootstrap: wipe and rebuild the test database before any test runs.
 *
 * THE PROBLEM (MariaDB InnoDB data dictionary bug on Windows):
 *   Dropping individual tables leaves ghost entries in InnoDB's data dictionary.
 *   Subsequent CREATE TABLE on those same names fails with "Table already exists"
 *   until InnoDB flushes its dict. With 337 migrations, several ghosts can appear.
 *
 * THE FIX:
 *   1. Drop tables one at a time (FK checks disabled) + FLUSH TABLES.
 *   2. Sleep briefly to let InnoDB flush.
 *   3. Run `migrate --force --no-ansi` in a subprocess (fresh TCP connection).
 *   4. Retry migrate up to N times:
 *      - Parse the error output for "Table 'X' already exists" ghost names.
 *      - Mark those migrations as done in the migrations registry so the next
 *        attempt skips them.
 *   5. Seed catalog/reference tables.
 *   6. Set RefreshDatabaseState::$migrated = true so RefreshDatabase uses
 *      per-test transactions for isolation instead of migrate:fresh.
 *
 * SAFETY: Protected by the allowedDatabases check below.
 */

require __DIR__.'/../vendor/autoload.php';

(function () {
    $app = require __DIR__.'/../bootstrap/app.php';
    $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    $connection = config('database.default');
    $database = config("database.connections.{$connection}.database");
    $allowedDbs = ['agro365_test'];

    if (! in_array($database, $allowedDbs, true)) {
        throw new RuntimeException(
            "🚨 PELIGRO: bootstrap refuses to wipe '{$database}'. ".
            'Only agro365_test is allowed. Check your .env.testing.'
        );
    }

    $host = config("database.connections.{$connection}.host");
    $port = config("database.connections.{$connection}.port", 3306);
    $username = config("database.connections.{$connection}.username");
    $password = config("database.connections.{$connection}.password");

    $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

    // ── Step 1: Drop every table one at a time (FK checks off).
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $pdo->exec('SET foreign_key_checks = 0');
    $tables = $pdo->query(
        "SELECT table_name FROM information_schema.tables
          WHERE table_schema = DATABASE() AND table_type = 'BASE TABLE'"
    )->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS `{$table}`");
    }
    $pdo->exec('SET foreign_key_checks = 1');

    // Flush table cache to help InnoDB release dict entries.
    try {
        $pdo->exec('FLUSH TABLES');
    } catch (\Throwable $e) { /* ignore */
    }
    unset($pdo);

    // ── Step 2: Brief pause to let InnoDB flush the data dictionary.
    sleep(3);

    // ── Step 3: Run migrate in a subprocess (fresh connection + plain output).
    putenv('APP_ENV=testing');
    $phpBin = PHP_BINARY;
    $artisan = dirname(__DIR__).'/artisan';
    $cmd = escapeshellarg($phpBin).' '.escapeshellarg($artisan).
               ' migrate --force --no-ansi 2>&1';

    // ── Step 4: Retry loop — each iteration fixes one ghost table.
    //
    // Strategy: parse error output for "Table 'X' already exists", then mark
    // that migration as done in the migrations registry so the next attempt skips
    // the CREATE TABLE for that ghost table.
    //
    // We only skip CREATE TABLE migrations; ALTER TABLE (add column/index) migrations
    // are NOT skipped even if the table is a ghost — they'd just fail again, which
    // is acceptable since ghost tables should not persist many iterations.
    $maxRetries = 50;
    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        $output = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);

        if ($exitCode === 0) {
            break;
        }

        if ($attempt === $maxRetries) {
            $detail = implode(PHP_EOL, array_slice($output, -30));
            throw new RuntimeException(
                "Bootstrap migration failed after {$maxRetries} attempts ".
                "(exit {$exitCode}):\n{$detail}"
            );
        }

        // ── Parse the error output for ghost table names (source: migrate output).
        $ghostTablesFromOutput = [];
        $outputStr = implode("\n", $output);
        if (preg_match_all(
            "/Table '(?:[^'.]+\.)?([^']+)' already exists/i",
            $outputStr,
            $matches
        )) {
            $ghostTablesFromOutput = array_unique($matches[1]);
        }

        // If we can't identify ghost tables from the output, wait and hope InnoDB
        // flushes on its own (covers edge cases with unusual error formats).
        if (empty($ghostTablesFromOutput)) {
            sleep(3);

            continue;
        }

        // ── Mark the ghost migrations as done so the next attempt skips them.
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        $migrationsTableExists = (bool) $pdo->query(
            "SELECT COUNT(*) FROM information_schema.tables
              WHERE table_schema = DATABASE() AND table_name = 'migrations'"
        )->fetchColumn();

        if (! $migrationsTableExists) {
            $pdo->exec(
                'CREATE TABLE `migrations` ('.
                '  `id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,'.
                '  `migration` varchar(255) NOT NULL,'.
                '  `batch` int NOT NULL'.
                ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
            );
        }

        $recorded = $pdo->query(
            'SELECT migration FROM migrations'
        )->fetchAll(PDO::FETCH_COLUMN);

        $migrationPath = dirname(__DIR__).'/database/migrations';
        $migrationFiles = glob("{$migrationPath}/*.php");
        sort($migrationFiles);

        foreach ($migrationFiles as $file) {
            $migName = basename($file, '.php');
            if (in_array($migName, $recorded, true)) {
                continue; // already recorded — skip
            }
            // Match "create_xxx_table" and "create_xxx" patterns.
            if (preg_match('/create_(.+?)(?:_table)?$/', $migName, $m)) {
                if (in_array($m[1], $ghostTablesFromOutput, true)) {
                    $pdo->exec(
                        'INSERT INTO `migrations` (migration, batch) VALUES ('.
                        $pdo->quote($migName).', 1)'
                    );
                }
            }
        }

        try {
            $pdo->exec('FLUSH TABLES');
        } catch (\Throwable $e) { /* ignore */
        }
        unset($pdo);
        sleep(2);
    }

    // ── Step 5: Seed catalog/reference data needed by tests.
    $kernel->call('db:seed', ['--force' => true]);
    $kernel->call('db:seed', ['--class' => 'AbilitySeeder', '--force' => true]);

    // ── Step 6: Tell RefreshDatabase that migrations are done.
    \Illuminate\Foundation\Testing\RefreshDatabaseState::$migrated = true;

    // ── Step 7: Restore the global error/exception handlers that the kernel
    // bootstrap above registered (via HandleExceptions). If left in place they
    // linger on the global handler stack, so PHPUnit's per-test snapshot counts
    // them; Laravel's per-test teardown (HandleExceptions::flushState) then
    // removes them and PHPUnit flags EVERY test risky with
    // "removed error handlers other than its own". Flushing here leaves the
    // stack clean before the first per-test snapshot is taken.
    \Illuminate\Foundation\Bootstrap\HandleExceptions::flushState();
})();
