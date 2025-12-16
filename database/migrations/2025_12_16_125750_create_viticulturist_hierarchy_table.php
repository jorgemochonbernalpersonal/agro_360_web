<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('viticulturist_hierarchy', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_viticulturist_id');
            $table->integer('child_viticulturist_id');
            $table->integer('winery_id');
            $table->integer('assigned_by')->nullable();
            $table->timestamps();

            $table->foreign('parent_viticulturist_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('child_viticulturist_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('winery_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
            
            $table->unique(['parent_viticulturist_id', 'child_viticulturist_id', 'winery_id']);
            
            $table->index('parent_viticulturist_id');
            $table->index('child_viticulturist_id');
            $table->index('winery_id');
        });

        // Check constraint: un viticultor no puede asignarse a sí mismo
        DB::statement('ALTER TABLE viticulturist_hierarchy ADD CONSTRAINT viticulturist_hierarchy_self_check CHECK (parent_viticulturist_id != child_viticulturist_id)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE viticulturist_hierarchy DROP CONSTRAINT IF EXISTS viticulturist_hierarchy_self_check');
        Schema::dropIfExists('viticulturist_hierarchy');
    }
};
