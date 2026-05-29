<?php
/**
 * Migrates user data from backup with remapped user IDs.
 * Run on the server: php migrate_users_data.php
 */

// Read DB credentials from .env
$env = [];
foreach (file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
    [$key, $val] = explode('=', $line, 2);
    $env[trim($key)] = trim($val, " \t\n\r\"'");
}

$pdo = new PDO(
    "mysql:host={$env['DB_HOST']};port={$env['DB_PORT']};dbname={$env['DB_DATABASE']};charset=utf8mb4",
    $env['DB_USERNAME'],
    $env['DB_PASSWORD']
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Old ID -> New ID
$user_map = [6335 => 1, 6329 => 2, 9 => 3];

// Tables to migrate: table => FK column (viticulturist_id or user_id)
$tables = [
    'plots'                  => 'viticulturist_id',
    'campaigns'              => 'viticulturist_id',
    'viticulturist_settings' => 'viticulturist_id',
    'onboarding_progress'    => 'user_id',
    'invoicing_settings'     => 'user_id',
    'user_taxes'             => 'user_id',
];

// Get column order from information_schema (reflects actual DB state after migrate:fresh)
$col_map = [];
foreach ($tables as $table => $fk) {
    $stmt = $pdo->query(
        "SELECT COLUMN_NAME FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$table'
         ORDER BY ORDINAL_POSITION"
    );
    $col_map[$table] = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Parse a SQL VALUES row string like (val1,'val2',NULL,3) into an array.
 */
function parse_row(string $row): array
{
    $row = trim($row, " \t\r\n(),;");
    $values = [];
    $i = 0;
    $len = strlen($row);

    while ($i < $len) {
        while ($i < $len && $row[$i] === ' ') $i++;
        if ($i >= $len) break;

        if ($row[$i] === "'") {
            $i++;
            $val = '';
            while ($i < $len) {
                if ($row[$i] === '\\' && $i + 1 < $len) {
                    $next = $row[$i + 1];
                    $val .= match($next) {
                        'n'  => "\n", 'r' => "\r", 't' => "\t",
                        '0'  => "\0", default => $next,
                    };
                    $i += 2;
                } elseif ($row[$i] === "'" && isset($row[$i + 1]) && $row[$i + 1] === "'") {
                    $val .= "'";
                    $i += 2;
                } elseif ($row[$i] === "'") {
                    $i++;
                    break;
                } else {
                    $val .= $row[$i++];
                }
            }
            $values[] = $val;
        } elseif (strtoupper(substr($row, $i, 4)) === 'NULL') {
            $values[] = null;
            $i += 4;
        } else {
            $j = $i;
            while ($j < $len && $row[$j] !== ',') $j++;
            $values[] = substr($row, $i, $j - $i);
            $i = $j;
        }

        while ($i < $len && ($row[$i] === ',' || $row[$i] === ' ')) $i++;
    }

    return $values;
}

$backup = __DIR__ . '/backup_29052026.sql';
$fh = fopen($backup, 'r');
$current_table = null;
$stats = [];

echo "Starting migration...\n\n";

while (($line = fgets($fh)) !== false) {
    $line = rtrim($line);

    // Detect table context: "INSERT INTO `table` VALUES"
    if (preg_match('/^INSERT INTO `([^`]+)` VALUES$/', $line, $m)) {
        $current_table = $m[1];
        continue;
    }

    // Reset on DDL statements
    if (preg_match('/^(--|UNLOCK|DROP TABLE|CREATE TABLE|LOCK TABLES|\/\*)/', $line)) {
        $current_table = null;
        continue;
    }

    if (!$current_table || !isset($tables[$current_table])) continue;

    $row_line = rtrim($line, ',;');
    if (!str_starts_with($row_line, '(')) continue;

    $vals = parse_row($row_line);
    $cols = $col_map[$current_table];
    $fk   = $tables[$current_table];
    $fk_pos = array_search($fk, $cols);

    if ($fk_pos === false || !array_key_exists($fk_pos, $vals)) continue;

    $old_id = (int)$vals[$fk_pos];
    if (!isset($user_map[$old_id])) continue;

    $new_id = $user_map[$old_id];
    $vals[$fk_pos] = $new_id;

    // Pad/trim values array to match column count
    $col_count = count($cols);
    while (count($vals) < $col_count) $vals[] = null;
    $vals = array_slice($vals, 0, $col_count);

    $placeholders = implode(',', array_fill(0, $col_count, '?'));
    $col_names    = implode(',', array_map(fn($c) => "`$c`", $cols));
    $sql          = "INSERT IGNORE INTO `$current_table` ($col_names) VALUES ($placeholders)";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($vals);
        $rows = $stmt->rowCount();
        if ($rows > 0) {
            $stats[$current_table] = ($stats[$current_table] ?? 0) + 1;
            echo "  ✓ {$current_table}: old_id={$old_id} → new_id={$new_id}\n";
        } else {
            echo "  ~ {$current_table}: skipped (duplicate) old_id={$old_id}\n";
        }
    } catch (Exception $e) {
        echo "  ✗ {$current_table}: " . $e->getMessage() . "\n";
        echo "    Row: " . substr($row_line, 0, 100) . "\n";
    }
}

fclose($fh);

echo "\n--- Summary ---\n";
if (empty($stats)) {
    echo "No rows migrated.\n";
} else {
    foreach ($stats as $t => $c) {
        echo "  {$t}: {$c} row(s)\n";
    }
}
echo "\nDone.\n";
