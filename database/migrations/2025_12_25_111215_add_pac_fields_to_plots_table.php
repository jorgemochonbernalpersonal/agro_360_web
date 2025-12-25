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
        Schema::table('plots', function (Blueprint $table) {
            // Superficie admisible PAC
            $table->decimal('pac_eligible_area', 10, 3)->nullable()->after('area')
                ->comment('Superficie admisible para ayudas PAC (excluye caminos, linderos, etc.)');
            
            $table->decimal('non_eligible_area', 10, 3)->default(0)->after('pac_eligible_area')
                ->comment('Superficie no admisible (caminos, construcciones, etc.)');
            
            $table->decimal('eligibility_coefficient', 5, 4)->default(1.0000)->after('non_eligible_area')
                ->comment('Coeficiente de admisibilidad (pac_eligible_area / area)');
            
            // Régimen de tenencia
            $table->string('tenure_regime')->default('propiedad')->after('eligibility_coefficient')
                ->comment('Régimen de tenencia: propiedad, arrendamiento, aparceria, cesion, usufructo');
            
            // Índices para búsquedas
            $table->index('tenure_regime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plots', function (Blueprint $table) {
            $table->dropIndex(['tenure_regime']);
            $table->dropColumn([
                'pac_eligible_area',
                'non_eligible_area',
                'eligibility_coefficient',
                'tenure_regime',
            ]);
        });
    }
};
