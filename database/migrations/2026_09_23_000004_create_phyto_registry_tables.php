<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Copia local del Registro Oficial de Productos Fitosanitarios del MAPA
 * (catálogo público, compartido por todos). No confundir con
 * phytosanitary_products, que son los productos que da de alta cada usuario.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phyto_registry_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('mapa_id')->unique();
            $table->string('registration_number', 20)->index();
            $table->string('name')->index();
            $table->string('holder')->nullable();
            $table->string('formulation', 500)->nullable();
            $table->string('status', 20)->index();
            $table->date('expires_on')->nullable();
            $table->text('conditions')->nullable();
            $table->json('composition')->nullable();
            // Copia en texto plano para buscar: el JSON distingue mayúsculas y escapa las tildes.
            $table->string('active_substances', 500)->nullable();
            $table->json('other_names')->nullable();
            $table->timestamps();
        });

        Schema::create('phyto_registry_uses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('phyto_registry_products')->cascadeOnDelete();
            $table->string('crop_code', 20)->index();
            $table->string('crop', 150);
            $table->string('agent_code', 20)->nullable();
            $table->string('agent', 200)->index();
            $table->decimal('dose_min', 12, 4)->nullable();
            $table->decimal('dose_max', 12, 4)->nullable();
            $table->string('dose_unit', 60)->nullable();
            $table->string('safety_period', 60)->nullable();
            $table->unsignedSmallInteger('safety_days')->nullable();
            $table->string('broth_volume', 500)->nullable();
            $table->string('applications', 60)->nullable();
            $table->string('application_interval', 60)->nullable();
            $table->string('bbch', 500)->nullable();
            $table->text('specific_conditions')->nullable();
            $table->string('user_type', 60)->nullable();
            $table->string('application_method', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phyto_registry_uses');
        Schema::dropIfExists('phyto_registry_products');
    }
};
