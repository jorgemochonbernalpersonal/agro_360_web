<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('do_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supervisor_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['pliego', 'reglamento']);
            $table->string('title');
            $table->string('version', 30)->nullable();
            $table->date('effective_date')->nullable();
            $table->text('content')->nullable();
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('do_documents');
    }
};
