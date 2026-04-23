<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('viticultor_assignments', function (Blueprint $table) {
            $table->renameColumn('viticultor_id', 'viticulturist_id');
        });

        Schema::rename('viticultor_assignments', 'viticulturist_assignments');
    }

    public function down(): void
    {
        Schema::rename('viticulturist_assignments', 'viticultor_assignments');

        Schema::table('viticultor_assignments', function (Blueprint $table) {
            $table->renameColumn('viticulturist_id', 'viticultor_id');
        });
    }
};
