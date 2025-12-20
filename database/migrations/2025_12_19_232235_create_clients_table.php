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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Tipo de cliente
            $table->enum('client_type', ['individual', 'company'])->default('individual');
            
            // Datos personales/empresa
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company_name')->nullable();
            $table->string('company_document')->nullable()->comment('CIF/NIF empresa');
            $table->string('particular_document')->nullable()->comment('DNI/NIE particular');
            
            // Información comercial
            $table->decimal('default_discount', 5, 2)->default(0)->comment('Descuento por defecto %');
            $table->enum('payment_method', ['cash', 'transfer', 'check', 'other'])->nullable();
            $table->string('account_number')->nullable()->comment('Número de cuenta para transferencias');
            
            // CAE (Canarias)
            $table->boolean('has_cae')->default(false)->comment('Tiene CAE (Canarias)');
            $table->string('cae_number')->nullable();
            
            // Estado
            $table->boolean('active')->default(true);
            $table->decimal('balance', 20, 2)->default(0)->comment('Saldo pendiente');
            $table->string('avatar')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('client_type');
            $table->index('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
