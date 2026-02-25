<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            // Validación intermedia (mitad de campaña)
            $table->boolean('mid_validation_signed')->default(false)->after('description');
            $table->datetime('mid_validation_date')->nullable()->after('mid_validation_signed');
            $table->foreignId('mid_validation_user_id')->nullable()->after('mid_validation_date')
                ->constrained('users')->onDelete('set null');

            // Validación final (cierre de campaña)
            $table->boolean('final_validation_signed')->default(false)->after('mid_validation_user_id');
            $table->datetime('final_validation_date')->nullable()->after('final_validation_signed');
            $table->foreignId('final_validation_user_id')->nullable()->after('final_validation_date')
                ->constrained('users')->onDelete('set null');

            // Bloqueo y PDF
            $table->datetime('locked_at')->nullable()->after('final_validation_user_id')
                ->comment('Cuando se cierra definitivamente la campaña — bloquea edición');
            $table->string('pdf_path')->nullable()->after('locked_at')
                ->comment('Ruta del PDF generado del cuaderno completo');
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropForeign(['mid_validation_user_id']);
            $table->dropForeign(['final_validation_user_id']);
            $table->dropColumn([
                'mid_validation_signed', 'mid_validation_date', 'mid_validation_user_id',
                'final_validation_signed', 'final_validation_date', 'final_validation_user_id',
                'locked_at', 'pdf_path',
            ]);
        });
    }
};
