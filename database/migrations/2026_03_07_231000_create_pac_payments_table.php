<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pac_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viticulturist_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('declaration_id')->nullable()->constrained('pac_declarations')->nullOnDelete();
            $table->unsignedSmallInteger('year');
            $table->enum('payment_type', [
                'basic_payment',
                'eco_scheme',
                'associated_aid',
                'transitional',
                'other',
            ])->default('basic_payment');
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->string('reference', 100)->nullable()->comment('Referencia FEGA/organismo pagador');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['viticulturist_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pac_payments');
    }
};
