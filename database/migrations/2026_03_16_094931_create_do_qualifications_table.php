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
        Schema::create('do_qualifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supervisor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('winery_id')->constrained('users')->cascadeOnDelete();

            $table->year('vintage');
            $table->string('wine_name');
            $table->enum('color', ['tinto', 'blanco', 'rosado', 'espumoso', 'dulce', 'otro'])->nullable();
            $table->decimal('alcohol_percentage', 4, 2)->nullable();
            $table->decimal('brix_degree', 5, 2)->nullable();
            $table->decimal('acidity_level', 5, 2)->nullable();
            $table->decimal('ph_level', 4, 2)->nullable();

            $table->unsignedTinyInteger('visual_score')->nullable();
            $table->unsignedTinyInteger('aroma_score')->nullable();
            $table->unsignedTinyInteger('taste_score')->nullable();
            $table->unsignedTinyInteger('overall_score')->nullable();

            $table->enum('result', ['qualified', 'disqualified', 'pending'])->default('pending');
            $table->text('tasting_notes')->nullable();
            $table->date('qualification_date')->nullable();
            $table->foreignId('qualified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['supervisor_id', 'result']);
            $table->index(['supervisor_id', 'vintage']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('do_qualifications');
    }
};
