<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Verificar que la tabla existe antes de intentar modificarla
        if (!Schema::hasTable('official_reports')) {
            return; // La tabla se creará en otra migración posterior
        }

        Schema::table('official_reports', function (Blueprint $table) {
            // Añadir processing_status si no existe
            if (!Schema::hasColumn('official_reports', 'processing_status')) {
                $table->enum('processing_status', ['pending', 'processing', 'completed', 'failed'])
                    ->default('completed')
                    ->after('invalidated_by')
                    ->comment('Estado del procesamiento en cola');
            }
            
            // Añadir processing_error si no existe
            if (!Schema::hasColumn('official_reports', 'processing_error')) {
                $table->text('processing_error')->nullable()->after('processing_status');
            }
            
            // Añadir completed_at si no existe
            if (!Schema::hasColumn('official_reports', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('processing_error');
            }
        });
        
        // Actualizar registros existentes que no tengan processing_status
        // Esto es necesario porque la columna tiene un default, pero los registros antiguos pueden tener NULL
        if (Schema::hasTable('official_reports')) {
            DB::table('official_reports')
                ->whereNull('processing_status')
                ->update(['processing_status' => 'completed']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('official_reports', function (Blueprint $table) {
            if (Schema::hasColumn('official_reports', 'completed_at')) {
                $table->dropColumn('completed_at');
            }
            if (Schema::hasColumn('official_reports', 'processing_error')) {
                $table->dropColumn('processing_error');
            }
            if (Schema::hasColumn('official_reports', 'processing_status')) {
                $table->dropColumn('processing_status');
            }
        });
    }
};
