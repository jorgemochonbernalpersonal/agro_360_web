<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('harvests', function (Blueprint $table) {
            $table->string('harvest_ticket_number', 50)->nullable()->after('notes')
                ->comment('Número de albarán / ticket de vendimia');

            // Estado sanitario desglosado (% del total cosechado)
            $table->decimal('sanitary_state_grapes', 5, 2)->nullable()->after('harvest_ticket_number')
                ->comment('% uva sana sobre el total cosechado');
            $table->decimal('sanitary_state_agraces', 5, 2)->nullable()->after('sanitary_state_grapes')
                ->comment('% agraces (uva verde/inmadura) sobre el total');
            $table->decimal('sanitary_state_botrytis', 5, 2)->nullable()->after('sanitary_state_agraces')
                ->comment('% afectado por botritis (podredumbre gris)');
            $table->decimal('sanitary_state_oidium', 5, 2)->nullable()->after('sanitary_state_botrytis')
                ->comment('% afectado por oídio (ceniza)');
            $table->decimal('sanitary_state_mildew', 5, 2)->nullable()->after('sanitary_state_oidium')
                ->comment('% afectado por mildiu');
        });
    }

    public function down(): void
    {
        Schema::table('harvests', function (Blueprint $table) {
            $table->dropColumn([
                'harvest_ticket_number',
                'sanitary_state_grapes',
                'sanitary_state_agraces',
                'sanitary_state_botrytis',
                'sanitary_state_oidium',
                'sanitary_state_mildew',
            ]);
        });
    }
};
