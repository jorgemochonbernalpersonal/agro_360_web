<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plots', function (Blueprint $table) {
            $table->decimal('pac_eligible_area', 10, 3)->nullable()->default(null)->change();
            $table->decimal('non_eligible_area', 10, 3)->nullable()->default(null)->change();
            $table->decimal('eligibility_coefficient', 5, 4)->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('plots', function (Blueprint $table) {
            $table->decimal('pac_eligible_area', 10, 3)->default(0)->change();
            $table->decimal('non_eligible_area', 10, 3)->default(0)->change();
            $table->decimal('eligibility_coefficient', 5, 4)->default(1.0000)->change();
        });
    }
};
