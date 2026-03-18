<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wine_losses', function (Blueprint $table) {
            $table->enum('loss_authorization', [
                'authorized',    // Pérdida autorizada reglamentariamente (evaporación)
                'processing',    // Pérdida por proceso enológico (filtración, clarificación)
                'extraordinary', // Pérdida extraordinaria (accidente, rotura)
                'quality',       // Rechazo por defecto de calidad
            ])->default('authorized')->after('loss_type');

            $table->string('regulatory_reference', 100)->nullable()
                ->comment('Nº expediente / referencia documental SILICIE')
                ->after('loss_authorization');
        });
    }

    public function down(): void
    {
        Schema::table('wine_losses', function (Blueprint $table) {
            $table->dropColumn(['loss_authorization', 'regulatory_reference']);
        });
    }
};
