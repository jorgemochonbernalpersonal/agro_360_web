<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Paso 1: Eliminar el constraint único temporalmente
        try {
            DB::statement('ALTER TABLE municipalities DROP INDEX municipalities_code_unique');
        } catch (\Exception $e) {
            // Si no existe, continuar
        }

        // Paso 2: Corregir códigos de 6 a 5 dígitos
        DB::statement('
            UPDATE municipalities 
            SET code = SUBSTRING(code, 1, 5) 
            WHERE LENGTH(code) = 6
        ');

        // Paso 3: Identificar y reportar duplicados
        $duplicates = DB::select("
            SELECT code, COUNT(*) as count 
            FROM municipalities 
            WHERE code IS NOT NULL AND code != ''
            GROUP BY code 
            HAVING COUNT(*) > 1
        ");

        if (! empty($duplicates)) {
            echo "\n⚠️  ADVERTENCIA: Se encontraron códigos duplicados:\n";
            foreach ($duplicates as $dup) {
                echo "  - Código {$dup->code}: {$dup->count} municipios\n";

                // Mostrar qué municipios tienen este código
                $muns = DB::select('
                    SELECT id, name 
                    FROM municipalities 
                    WHERE code = ? 
                    LIMIT 5
                ', [$dup->code]);

                foreach ($muns as $mun) {
                    echo "    * ID {$mun->id}: {$mun->name}\n";
                }
            }
            echo "\n💡 Estos duplicados deben ser corregidos manualmente.\n";
            echo "   Los códigos de municipios deben ser únicos según el INE.\n\n";
        }

        // Paso 4: Intentar restaurar el constraint único (fallará si hay duplicados)
        try {
            DB::statement('ALTER TABLE municipalities ADD UNIQUE INDEX municipalities_code_unique (code)');
            echo "✅ Constraint único restaurado exitosamente.\n";
        } catch (\Exception $e) {
            echo "⚠️  No se pudo restaurar el constraint único debido a duplicados.\n";
            echo "   Debes corregir los duplicados manualmente antes de añadir el constraint.\n";
        }

        // Log final
        $total = DB::table('municipalities')->count();
        $with5 = DB::table('municipalities')->whereRaw('LENGTH(code) = 5')->count();
        echo "\n📊 Resumen:\n";
        echo "   Total municipios: {$total}\n";
        echo "   Con código de 5 dígitos: {$with5}\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        echo "⚠️  ADVERTENCIA: No se puede revertir esta migración sin un backup de la base de datos.\n";
        echo "   Los códigos originales de 6 dígitos no se pueden recuperar automáticamente.\n";
    }
};
