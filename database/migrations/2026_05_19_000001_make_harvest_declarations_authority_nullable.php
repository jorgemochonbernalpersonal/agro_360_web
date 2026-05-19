<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('harvest_declarations', function (Blueprint $table) {
            $table->string('authority')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('harvest_declarations', function (Blueprint $table) {
            $table->string('authority')->nullable(false)->change();
        });
    }
};
