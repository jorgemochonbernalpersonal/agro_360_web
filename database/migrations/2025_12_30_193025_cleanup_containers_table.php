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
        Schema::table('containers', function (Blueprint $table) {
            // Eliminar supplier_id (sin tabla suppliers)
            $table->dropColumn('supplier_id');
            
            // Añadir supplier_name para reemplazar supplier_id
            $table->string('supplier_name')->nullable()->after('next_maintenance_date')
                ->comment('Nombre del proveedor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('containers', function (Blueprint $table) {
            // Restaurar supplier_id
            $table->unsignedBigInteger('supplier_id')->nullable()->after('user_id');
            
            // Eliminar supplier_name
            $table->dropColumn('supplier_name');
        });
    }
};
