<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Each column addition is guarded with hasColumn() to survive MariaDB's
        // non-transactional DDL: if a prior run partially completed, we skip
        // already-added columns instead of failing with "Duplicate column name".
        if (! Schema::hasColumn('plots', 'soil_type_id')) {
            Schema::table('plots', function (Blueprint $table) {
                $table->foreignId('soil_type_id')->nullable()->after('soil_type')->constrained('soil_types')->nullOnDelete();
            });
        }
        if (! Schema::hasColumn('plots', 'irrigation_type_id')) {
            Schema::table('plots', function (Blueprint $table) {
                $table->foreignId('irrigation_type_id')->nullable()->after('soil_type_id')->constrained('irrigation_types')->nullOnDelete();
            });
        }
        if (! Schema::hasColumn('plots', 'topography_id')) {
            Schema::table('plots', function (Blueprint $table) {
                $table->foreignId('topography_id')->nullable()->after('irrigation_type_id')->constrained('topographies')->nullOnDelete();
            });
        }
        if (! Schema::hasColumn('plots', 'property_type_id')) {
            Schema::table('plots', function (Blueprint $table) {
                // tenure_regime is dropped in migration 000005; use nullable after
                $table->foreignId('property_type_id')->nullable()->constrained('property_types')->nullOnDelete();
            });
        }
        if (! Schema::hasColumn('plots', 'valley_id')) {
            Schema::table('plots', function (Blueprint $table) {
                $table->foreignId('valley_id')->nullable()->after('valley')->constrained('valleys')->nullOnDelete();
            });
        }
        if (! Schema::hasColumn('plots', 'site_id')) {
            Schema::table('plots', function (Blueprint $table) {
                $table->foreignId('site_id')->nullable()->after('site_name')->constrained('sites')->nullOnDelete();
            });
        }
        if (! Schema::hasColumn('plots', 'training_system_id')) {
            Schema::table('plots', function (Blueprint $table) {
                $table->foreignId('training_system_id')->nullable()->after('site_id')->constrained('training_systems')->nullOnDelete();
            });
        }
        if (! Schema::hasColumn('plots', 'owner_id')) {
            Schema::table('plots', function (Blueprint $table) {
                $table->unsignedBigInteger('owner_id')->nullable()->after('viticulturist_id');
                $table->foreign('owner_id')->references('id')->on('users')->nullOnDelete();
            });
        }
        if (! Schema::hasColumn('plots', 'enclosure')) {
            Schema::table('plots', function (Blueprint $table) {
                $table->string('enclosure', 100)->nullable()->after('code_parcel')->comment('Referencia de recinto/enclave');
            });
        }
        if (! Schema::hasColumn('plots', 'place')) {
            Schema::table('plots', function (Blueprint $table) {
                $table->string('place', 255)->nullable()->after('enclosure')->comment('Lugar o paraje libre');
            });
        }
        if (! Schema::hasColumn('plots', 'planting_pattern')) {
            Schema::table('plots', function (Blueprint $table) {
                $table->string('planting_pattern', 50)->nullable()->after('training_system_id')->comment('Marco de plantación: cuadrado, tresbolillo...');
            });
        }
        if (! Schema::hasColumn('plots', 'slope')) {
            Schema::table('plots', function (Blueprint $table) {
                $table->decimal('slope', 5, 2)->nullable()->after('planting_pattern')->comment('Pendiente en %');
            });
        }
        if (! Schema::hasColumn('plots', 'number_of_vines')) {
            Schema::table('plots', function (Blueprint $table) {
                $table->integer('number_of_vines')->nullable()->after('slope')->comment('Número total de cepas en la parcela');
            });
        }
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
