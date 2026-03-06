<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla de orientaciones
        Schema::create('orientations', function (Blueprint $table) {
            $table->id();
            $table->string('name');           // "Norte", "Noreste", etc.
            $table->string('abbreviation', 3); // "N", "NE", etc.
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Añadir orientation_id y quitar orientation varchar + tenure_regime
        Schema::table('plots', function (Blueprint $table) {
            $table->foreignId('orientation_id')->nullable()->constrained('orientations')->nullOnDelete()->after('topography_id');
            $table->dropColumn(['orientation', 'tenure_regime']);
        });
    }

    public function down(): void
    {
        Schema::table('plots', function (Blueprint $table) {
            $table->dropForeign(['orientation_id']);
            $table->dropColumn('orientation_id');
            $table->string('orientation')->nullable()->after('topography_id');
            $table->string('tenure_regime')->nullable()->after('property_type_id');
        });

        Schema::dropIfExists('orientations');
    }
};
