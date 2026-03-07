<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pac_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viticulturist_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->string('reference_number', 50)->nullable()->comment('Referencia FEGA/organismo pagador');
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->decimal('total_declared_area', 10, 3)->default(0);
            $table->decimal('total_eligible_area', 10, 3)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['viticulturist_id', 'year']);
            $table->unique(['viticulturist_id', 'year'], 'pac_declarations_vitic_year_unique');
        });

        Schema::create('pac_declaration_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('declaration_id')->constrained('pac_declarations')->cascadeOnDelete();
            $table->foreignId('plot_id')->constrained('plots')->cascadeOnDelete();
            $table->decimal('declared_area', 10, 3);
            $table->decimal('eligible_area', 10, 3)->default(0);
            $table->json('eco_schemes')->nullable()->comment('Array de eco-regímenes declarados');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['declaration_id', 'plot_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pac_declaration_items');
        Schema::dropIfExists('pac_declarations');
    }
};
