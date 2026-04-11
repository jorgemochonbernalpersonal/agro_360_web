<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('supervisor_winery_notes')) {
            return;
        }

        Schema::create('supervisor_winery_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supervisor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('winery_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['visit', 'call', 'warning', 'note'])->default('note');
            $table->date('note_date');
            $table->text('content');
            $table->timestamps();

            $table->index(['supervisor_id', 'winery_id', 'note_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supervisor_winery_notes');
    }
};
