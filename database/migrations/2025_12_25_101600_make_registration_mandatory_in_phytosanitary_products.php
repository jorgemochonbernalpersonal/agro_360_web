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
        Schema::table('phytosanitary_products', function (Blueprint $table) {
            // Hacer obligatorio el número de registro (Real Decreto 1311/2012)
            $table->string('registration_number', 100)->nullable(false)->change();
            
            // Hacer obligatorio el plazo de seguridad (seguridad alimentaria)
            $table->integer('withdrawal_period_days')->nullable(false)->default(0)->change();
            
            // Añadir fecha de caducidad del registro y estado
            $table->date('registration_expiry_date')->nullable()->after('registration_number');
            $table->enum('registration_status', ['active', 'expired', 'revoked'])
                ->default('active')
                ->after('registration_expiry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('phytosanitary_products', function (Blueprint $table) {
            $table->string('registration_number', 100)->nullable()->change();
            $table->integer('withdrawal_period_days')->nullable()->change();
            $table->dropColumn(['registration_expiry_date', 'registration_status']);
        });
    }
};
