<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plots', function (Blueprint $table) {
            // FK lookup fields (replace existing string fields)
            $table->foreignId('soil_type_id')->nullable()->after('soil_type')->constrained('soil_types')->nullOnDelete();
            $table->foreignId('irrigation_type_id')->nullable()->after('soil_type_id')->constrained('irrigation_types')->nullOnDelete();
            $table->foreignId('topography_id')->nullable()->after('irrigation_type_id')->constrained('topographies')->nullOnDelete();
            $table->foreignId('property_type_id')->nullable()->after('tenure_regime')->constrained('property_types')->nullOnDelete();
            $table->foreignId('valley_id')->nullable()->after('valley')->constrained('valleys')->nullOnDelete();
            $table->foreignId('site_id')->nullable()->after('site_name')->constrained('sites')->nullOnDelete();
            $table->foreignId('training_system_id')->nullable()->after('site_id')->constrained('training_systems')->nullOnDelete();
            $table->unsignedBigInteger('owner_id')->nullable()->after('viticulturist_id');
            $table->foreign('owner_id')->references('id')->on('users')->nullOnDelete();

            // New simple fields
            $table->string('enclosure', 100)->nullable()->after('code_parcel')->comment('Referencia de recinto/enclave');
            $table->string('place', 255)->nullable()->after('enclosure')->comment('Lugar o paraje libre');
            $table->string('planting_pattern', 50)->nullable()->after('training_system_id')->comment('Marco de plantación: cuadrado, tresbolillo...');
            $table->decimal('slope', 5, 2)->nullable()->after('planting_pattern')->comment('Pendiente en %');
            $table->integer('number_of_vines')->nullable()->after('slope')->comment('Número total de cepas en la parcela');
        });
    }

    public function down(): void
    {
        Schema::table('plots', function (Blueprint $table) {
            $table->dropForeign(['soil_type_id']);
            $table->dropForeign(['irrigation_type_id']);
            $table->dropForeign(['topography_id']);
            $table->dropForeign(['property_type_id']);
            $table->dropForeign(['valley_id']);
            $table->dropForeign(['site_id']);
            $table->dropForeign(['training_system_id']);
            $table->dropForeign(['owner_id']);
            $table->dropColumn([
                'soil_type_id', 'irrigation_type_id', 'topography_id', 'property_type_id',
                'valley_id', 'site_id', 'training_system_id', 'owner_id',
                'enclosure', 'place', 'planting_pattern', 'slope', 'number_of_vines',
            ]);
        });
    }
};
