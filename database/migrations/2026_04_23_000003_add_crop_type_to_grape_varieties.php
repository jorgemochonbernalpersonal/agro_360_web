<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grape_varieties', function (Blueprint $table) {
            $table->string('crop_type', 20)->default('wine')->after('color');
            $table->index('crop_type');
        });
    }

    public function down(): void
    {
        Schema::table('grape_varieties', function (Blueprint $table) {
            $table->dropIndex(['crop_type']);
            $table->dropColumn('crop_type');
        });
    }
};
