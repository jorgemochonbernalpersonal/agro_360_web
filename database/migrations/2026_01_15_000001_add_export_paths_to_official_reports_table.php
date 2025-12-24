<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Verificar que la tabla existe
        if (!Schema::hasTable('official_reports')) {
            return;
        }

        Schema::table('official_reports', function (Blueprint $table) {
            // Añadir columnas para rutas de exportación CSV y XML si no existen
            if (!Schema::hasColumn('official_reports', 'csv_path')) {
            $table->string('csv_path')->nullable()->after('pdf_filename');
            }
            if (!Schema::hasColumn('official_reports', 'xml_path')) {
            $table->string('xml_path')->nullable()->after('csv_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('official_reports', function (Blueprint $table) {
            $table->dropColumn(['csv_path', 'xml_path']);
        });
    }
};
