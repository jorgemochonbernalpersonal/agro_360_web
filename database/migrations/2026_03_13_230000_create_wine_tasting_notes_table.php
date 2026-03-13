<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wine_tasting_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('oenologist_id')->nullable()->constrained()->nullOnDelete();
            $table->date('evaluation_date');
            $table->string('evaluator_name', 255)->nullable();

            // Visual
            $table->string('visual_color', 100)->nullable();
            $table->enum('visual_clarity', ['brilliant', 'clear', 'slightly_hazy', 'hazy'])->nullable();
            $table->enum('visual_intensity', ['pale', 'medium', 'deep', 'very_deep'])->nullable();

            // Olfativo
            $table->enum('aroma_intensity', ['light', 'medium', 'pronounced', 'complex'])->nullable();
            $table->text('aroma_descriptors')->nullable();

            // Gustativo
            $table->enum('palate_acidity', ['low', 'medium_minus', 'medium', 'medium_plus', 'high'])->nullable();
            $table->enum('palate_tannins', ['low', 'medium_minus', 'medium', 'medium_plus', 'high'])->nullable();
            $table->enum('palate_body', ['light', 'medium', 'full'])->nullable();
            $table->enum('palate_finish', ['short', 'medium', 'long'])->nullable();

            // Valoración global
            $table->decimal('overall_score', 4, 1)->nullable();   // 0–100
            $table->text('overall_conclusion')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wine_tasting_notes');
    }
};
