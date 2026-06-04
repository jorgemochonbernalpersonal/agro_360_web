<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Reconstruye la columna `code` de sigpac_code con el formato correcto de 24 dígitos.
     *
     * Formato antiguo (incorrecto): CA(2)+Prov(2)+Mun(3)+Agr(1)+Zona(1)+Pol(2)+Parcela(5)+Recinto(3) = 19 dígitos
     * Formato nuevo  (correcto):    CA(2)+Prov(2)+Mun(3)+Agr(3)+Zona(3)+Pol(3)+Parcela(5)+Recinto(3) = 24 dígitos
     *
     * Los campos individuales (code_aggregate, code_zone, code_polygon) ya están correctamente
     * almacenados en columnas VARCHAR(10); solo el campo concatenado `code` era incorrecto.
     */
    public function up(): void
    {
        DB::table('sigpac_code')->orderBy('id')->each(function (object $record) {
            $newCode = implode('', [
                str_pad($record->code_autonomous_community ?? '', 2, '0', STR_PAD_LEFT),
                str_pad($record->code_province ?? '', 2, '0', STR_PAD_LEFT),
                str_pad($record->code_municipality ?? '', 3, '0', STR_PAD_LEFT),
                str_pad($record->code_aggregate ?? '0', 3, '0', STR_PAD_LEFT),
                str_pad($record->code_zone ?? '0', 3, '0', STR_PAD_LEFT),
                str_pad($record->code_polygon ?? '', 3, '0', STR_PAD_LEFT),
                str_pad($record->code_plot ?? '', 5, '0', STR_PAD_LEFT),
                str_pad($record->code_enclosure ?? '', 3, '0', STR_PAD_LEFT),
            ]);

            DB::table('sigpac_code')
                ->where('id', $record->id)
                ->update(['code' => $newCode]);
        });
    }

    public function down(): void
    {
        // Reconstruye al formato antiguo de 19 dígitos (para rollback)
        DB::table('sigpac_code')->orderBy('id')->each(function (object $record) {
            $oldCode = implode('', [
                str_pad($record->code_autonomous_community ?? '', 2, '0', STR_PAD_LEFT),
                str_pad($record->code_province ?? '', 2, '0', STR_PAD_LEFT),
                str_pad($record->code_municipality ?? '', 3, '0', STR_PAD_LEFT),
                str_pad($record->code_aggregate ?? '0', 1, '0', STR_PAD_LEFT),
                str_pad($record->code_zone ?? '0', 1, '0', STR_PAD_LEFT),
                str_pad($record->code_polygon ?? '', 2, '0', STR_PAD_LEFT),
                str_pad($record->code_plot ?? '', 5, '0', STR_PAD_LEFT),
                str_pad($record->code_enclosure ?? '', 3, '0', STR_PAD_LEFT),
            ]);

            DB::table('sigpac_code')
                ->where('id', $record->id)
                ->update(['code' => $oldCode]);
        });
    }
};
