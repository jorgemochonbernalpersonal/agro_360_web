<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Generaliza la tabla de enlace de geometrías para admitir geometrías que NO
 * provienen de SIGPAC (p. ej. del Catastro, a partir de plots.code_parcel).
 *
 *  - sigpac_code_id pasa a ser NULLABLE: una geometría catastral/manual no
 *    tiene código SIGPAC asociado.
 *  - Se añade `source` para distinguir el origen de cada geometría enlazada.
 *
 * La FK fk_multiple_plot_sigpac_sigpac (ON DELETE CASCADE) sigue siendo válida:
 * una FK no exige NOT NULL; un valor NULL simplemente no referencia nada.
 */
return new class extends Migration
{
    public function up(): void
    {
        // sigpac_code_id NOT NULL -> NULL (mantiene tipo y FK existentes)
        DB::statement(
            'ALTER TABLE `multipart_plot_sigpac` '
            .'MODIFY `sigpac_code_id` BIGINT(20) UNSIGNED NULL'
        );

        Schema::table('multipart_plot_sigpac', function (Blueprint $table) {
            $table->enum('source', ['sigpac', 'catastro', 'manual'])
                ->default('sigpac')
                ->after('sigpac_code_id')
                ->comment('Origen de la geometría enlazada');
        });
    }

    public function down(): void
    {
        // Las filas sin SIGPAC (catastro/manual) no pueden existir con la
        // columna en NOT NULL: se eliminan antes de revertir.
        DB::table('multipart_plot_sigpac')->whereNull('sigpac_code_id')->delete();

        Schema::table('multipart_plot_sigpac', function (Blueprint $table) {
            $table->dropColumn('source');
        });

        DB::statement(
            'ALTER TABLE `multipart_plot_sigpac` '
            .'MODIFY `sigpac_code_id` BIGINT(20) UNSIGNED NOT NULL'
        );
    }
};
