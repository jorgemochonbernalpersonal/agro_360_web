<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // La FK puede existir ya si fue creada en la migración original de la tabla
        Schema::table('agricultural_activities', function (Blueprint $table) {
            $fks = collect(\DB::select("
                SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'agricultural_activities'
                AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                AND CONSTRAINT_NAME = 'agricultural_activities_machinery_id_foreign'
            "));

            if ($fks->isEmpty()) {
                $table->foreign('machinery_id')
                    ->references('id')
                    ->on('machinery')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('agricultural_activities', function (Blueprint $table) {
            $table->dropForeign(['machinery_id']);
        });
    }
};
