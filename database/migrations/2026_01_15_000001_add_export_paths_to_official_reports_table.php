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
        Schema::table('official_reports', function (Blueprint $table) {
            // Añadir columnas para rutas de exportación CSV y XML
            $table->string('csv_path')->nullable()->after('pdf_filename');
            $table->string('xml_path')->nullable()->after('csv_path');
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
