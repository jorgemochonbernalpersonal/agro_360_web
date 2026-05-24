<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Convierte columnas de catálogo administrado a JSON para soporte multilingüe.
     * Los valores existentes se migran al locale 'es' dentro del JSON.
     */
    public function up(): void
    {
        // grape_varieties y pests ya tienen columnas JSON de migraciones previas — solo
        // aseguramos que los datos existentes estén envueltos en {"es": ...}.
        $this->wrapExistingDataInLocale('grape_varieties', 'name');
        $this->wrapExistingDataInLocale('grape_varieties', 'description');
        $this->wrapExistingDataInLocale('pests', 'name');
        $this->wrapExistingDataInLocale('pests', 'description');
        $this->wrapExistingDataInLocale('pests', 'symptoms');
        $this->wrapExistingDataInLocale('pests', 'lifecycle');
        $this->wrapExistingDataInLocale('pests', 'prevention_methods');

        // ── container_types ──────────────────────────────────────────────────
        $ctRows = DB::table('container_types')->select('id', 'name', 'description')->get();
        $this->dropIndexIfExists('container_types', 'container_types_name_unique');
        DB::statement('ALTER TABLE container_types MODIFY COLUMN name LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL');
        DB::statement('ALTER TABLE container_types MODIFY COLUMN description LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL');
        foreach ($ctRows as $row) {
            if ($row->name !== null && !$this->isJson($row->name)) {
                DB::table('container_types')->where('id', $row->id)->update([
                    'name' => json_encode(['es' => $row->name], JSON_UNESCAPED_UNICODE),
                ]);
            }
            if ($row->description !== null && !$this->isJson($row->description)) {
                DB::table('container_types')->where('id', $row->id)->update([
                    'description' => json_encode(['es' => $row->description], JSON_UNESCAPED_UNICODE),
                ]);
            }
        }

        // ── machinery_types ──────────────────────────────────────────────────
        $mtRows = DB::table('machinery_types')->select('id', 'name')->get();
        $this->dropIndexIfExists('machinery_types', 'machinery_types_name_unique');
        DB::statement('ALTER TABLE machinery_types MODIFY COLUMN name LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL');
        foreach ($mtRows as $row) {
            if ($row->name !== null && !$this->isJson($row->name)) {
                DB::table('machinery_types')->where('id', $row->id)->update([
                    'name' => json_encode(['es' => $row->name], JSON_UNESCAPED_UNICODE),
                ]);
            }
        }
    }

    public function down(): void
    {
        // No revertimos grape_varieties ni pests: ya eran JSON antes de esta migración.

        // container_types
        $ctRows = DB::table('container_types')->select('id', 'name', 'description')->get();
        DB::statement('ALTER TABLE container_types MODIFY COLUMN name VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE container_types MODIFY COLUMN description VARCHAR(255) NULL');
        DB::statement('ALTER TABLE container_types ADD UNIQUE INDEX container_types_name_unique (name)');
        foreach ($ctRows as $row) {
            if ($row->name !== null && $this->isJson($row->name)) {
                $decoded = json_decode($row->name, true);
                DB::table('container_types')->where('id', $row->id)->update([
                    'name' => $decoded['es'] ?? array_values($decoded)[0] ?? null,
                ]);
            }
            if ($row->description !== null && $this->isJson($row->description)) {
                $decoded = json_decode($row->description, true);
                DB::table('container_types')->where('id', $row->id)->update([
                    'description' => $decoded['es'] ?? array_values($decoded)[0] ?? null,
                ]);
            }
        }

        // machinery_types
        $mtRows = DB::table('machinery_types')->select('id', 'name')->get();
        DB::statement('ALTER TABLE machinery_types MODIFY COLUMN name VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE machinery_types ADD UNIQUE INDEX machinery_types_name_unique (name)');
        foreach ($mtRows as $row) {
            if ($row->name !== null && $this->isJson($row->name)) {
                $decoded = json_decode($row->name, true);
                DB::table('machinery_types')->where('id', $row->id)->update([
                    'name' => $decoded['es'] ?? array_values($decoded)[0] ?? null,
                ]);
            }
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function migrateStringToJson(string $table, string $column): void
    {
        $rows = DB::table($table)->select('id', $column)->get();

        Schema::table($table, function (Blueprint $t) use ($column) {
            $t->json($column)->nullable()->change();
        });

        foreach ($rows as $row) {
            $value = $row->{$column};
            if ($value !== null && !$this->isJson($value)) {
                DB::table($table)->where('id', $row->id)->update([
                    $column => json_encode(['es' => $value], JSON_UNESCAPED_UNICODE),
                ]);
            }
        }
    }

    private function migrateTextToJson(string $table, string $column): void
    {
        $rows = DB::table($table)->select('id', $column)->get();

        Schema::table($table, function (Blueprint $t) use ($column) {
            $t->json($column)->nullable()->change();
        });

        foreach ($rows as $row) {
            $value = $row->{$column};
            if ($value !== null && !$this->isJson($value)) {
                DB::table($table)->where('id', $row->id)->update([
                    $column => json_encode(['es' => $value], JSON_UNESCAPED_UNICODE),
                ]);
            }
        }
    }

    private function revertJsonToString(string $table, string $column): void
    {
        $rows = DB::table($table)->select('id', $column)->get();

        Schema::table($table, function (Blueprint $t) use ($column) {
            $t->string($column)->nullable()->change();
        });

        foreach ($rows as $row) {
            $value = $row->{$column};
            if ($value !== null && $this->isJson($value)) {
                $decoded = json_decode($value, true);
                DB::table($table)->where('id', $row->id)->update([
                    $column => $decoded['es'] ?? array_values($decoded)[0] ?? null,
                ]);
            }
        }
    }

    private function revertJsonToText(string $table, string $column): void
    {
        $rows = DB::table($table)->select('id', $column)->get();

        Schema::table($table, function (Blueprint $t) use ($column) {
            $t->text($column)->nullable()->change();
        });

        foreach ($rows as $row) {
            $value = $row->{$column};
            if ($value !== null && $this->isJson($value)) {
                $decoded = json_decode($value, true);
                DB::table($table)->where('id', $row->id)->update([
                    $column => $decoded['es'] ?? array_values($decoded)[0] ?? null,
                ]);
            }
        }
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        $exists = DB::select(
            "SELECT COUNT(*) as cnt FROM information_schema.statistics
             WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?",
            [$table, $index]
        );
        if ($exists[0]->cnt > 0) {
            DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$index}`");
        }
    }

    private function wrapExistingDataInLocale(string $table, string $column): void
    {
        DB::table($table)->select('id', $column)->get()->each(function ($row) use ($table, $column) {
            $value = $row->{$column};
            if ($value !== null && !$this->isJson($value)) {
                DB::table($table)->where('id', $row->id)->update([
                    $column => json_encode(['es' => $value], JSON_UNESCAPED_UNICODE),
                ]);
            }
        });
    }

    private function isJson(string $value): bool
    {
        if (!str_starts_with(trim($value), '{')) {
            return false;
        }
        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }
};
