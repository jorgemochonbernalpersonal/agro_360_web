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
        Schema::table('user_profiles', function (Blueprint $table) {
            // Eliminar columna province (texto)
            $table->dropColumn('province');
            
            // Agregar columna province_id (foreign key)
            $table->foreignId('province_id')->nullable()->after('postal_code')->constrained('provinces')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            // Revertir: eliminar province_id y agregar province
            $table->dropForeign(['province_id']);
            $table->dropColumn('province_id');
            $table->string('province')->nullable()->after('postal_code');
        });
    }
};
