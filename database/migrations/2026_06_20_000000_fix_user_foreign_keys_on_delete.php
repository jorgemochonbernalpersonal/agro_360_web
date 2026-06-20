<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Alinea las últimas FKs hacia `users` que quedaron en RESTRICT (sin acción
 * ON DELETE) con la convención del resto del esquema, donde borrar un usuario
 * propaga en cascada el borrado de sus datos. Sin esto, eliminar un viticultor
 * con campañas/actividades reventaba con SQLSTATE[23000] 1451.
 *
 *   - Columnas "dueño" (viticulturist_id / user_id / created_by) → cascade
 *   - Columnas "actor" anulables (invalidated_by / locked_by)    → set null
 *
 * Cada cambio comprueba primero si la FK existe, de modo que la migración es
 * re-ejecutable (algunas columnas, p.ej. locked_by, nunca llegaron a tener FK).
 */
return new class extends Migration
{
    /**
     * Tabla => [columna => acción onDelete].
     *
     * @var array<string, array<string, 'cascade'|'set null'>>
     */
    private array $map = [
        'campaigns' => ['viticulturist_id' => 'cascade'],
        'agricultural_activities' => ['viticulturist_id' => 'cascade'],
        'product_stock_movements' => ['user_id' => 'cascade'],
        'wine_subproducts' => ['created_by' => 'cascade'],
        'official_reports' => ['invalidated_by' => 'set null'],
        'agricultural_activity_audit_logs' => ['locked_by' => 'set null'],
    ];

    public function up(): void
    {
        foreach ($this->map as $table => $columns) {
            foreach ($columns as $column => $action) {
                $this->resetForeignKey($table, $column, $action);
            }
        }
    }

    public function down(): void
    {
        // Revertir a RESTRICT (FK sin acción ON DELETE).
        foreach ($this->map as $table => $columns) {
            foreach ($columns as $column => $action) {
                $this->resetForeignKey($table, $column, null);
            }
        }
    }

    /**
     * Recrea la FK columna→users.id con la acción ON DELETE indicada,
     * dropeando primero la existente solo si la hay.
     */
    private function resetForeignKey(string $table, string $column, ?string $action): void
    {
        if (! Schema::hasColumn($table, $column)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($table, $column, $action) {
            if ($this->foreignKeyExists($table, $column)) {
                $blueprint->dropForeign([$column]);
            }

            $foreign = $blueprint->foreign($column)->references('id')->on('users');

            if ($action === 'cascade') {
                $foreign->cascadeOnDelete();
            } elseif ($action === 'set null') {
                $foreign->nullOnDelete();
            }
        });
    }

    private function foreignKeyExists(string $table, string $column): bool
    {
        foreach (Schema::getForeignKeys($table) as $foreignKey) {
            if (in_array($column, $foreignKey['columns'], true)) {
                return true;
            }
        }

        return false;
    }
};
