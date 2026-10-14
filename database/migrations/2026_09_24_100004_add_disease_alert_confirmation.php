<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Doble opt-in para las suscripciones públicas a alertas de mildiu/oídio
 * (app/Livewire/Tools/DiseaseAlerts.php): antes se activaban de inmediato, sin
 * confirmación por email, lo que permitía suscribir la dirección de un tercero sin su
 * consentimiento (el límite de 5/hora/IP es trivialmente evitable rotando de IP).
 *
 * Con estas columnas, una suscripción nueva se guarda con confirmed_at = null y un
 * confirmation_token; solo se considera "activa" para el envío real de alertas cuando
 * el destinatario confirma desde el enlace que recibe por email.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('disease_alert_subscriptions', function (Blueprint $table) {
            $table->string('confirmation_token', 64)->nullable()->unique()->after('token');
            $table->timestamp('confirmed_at')->nullable()->after('confirmation_token');
        });
    }

    public function down(): void
    {
        Schema::table('disease_alert_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['confirmation_token', 'confirmed_at']);
        });
    }
};
